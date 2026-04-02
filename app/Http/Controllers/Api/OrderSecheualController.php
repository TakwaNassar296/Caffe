<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Branch;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Services\StripeService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Api\OrderResource;
use App\Http\Requests\Api\OrderScheduleRequest;
use App\Http\Requests\Api\Order\CheckoutRequest;

class OrderSecheualController extends Controller
{
    use ApiResponse;


    public $stripeService;
    public function __construct (StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    public function index()
    {
        $user = Auth::guard('user')->user();
        $orders = $user->orders()
            ->where('status', 'scheduled')
            ->where('payment_status', 'pending')
            ->with(['items.product', 'branch'])
            ->paginate(10);
        return $this->PaginationResponse(OrderResource::collection($orders), 'Scheduled orders', 200);
    }



    public function scheduleOrder(OrderScheduleRequest $request)
    {
        $user = Auth::guard('user')->user();
        $data = $request->validated();
    
        $branch = Branch::where('id', $data['branch_id'])->first();
    
        if (!$branch) {
            return $this->errorResponse('Branch not found', 404);
        }
    
        $scheduleTime = Carbon::parse($data['schedual_date']);
        $scheduleHour = $scheduleTime->format('H:i:s');
    
        if (!($scheduleHour >= $branch->opening_time && $scheduleHour <= $branch->close_date)) {
            return $this->errorResponse("Selected time is outside branch working hours ({$branch->opening_time} - {$branch->close_date})");
        }
    
        DB::beginTransaction();
        try {
            // Handle payment method
            if ($data['pay_with'] === 'points') {

                // For points payment, validate and deduct points immediately
                $totalPointsCost = $this->calculatePointsCostFromProducts($data['products']);
                $this->validateAndDeductPoints($user, $totalPointsCost);

                $subtotal = 0;
                $deliveryCharge = 0;
                $tax = 0;
                $finalTotal = 0;
            } else {
                // For money payment, calculate normal charges
                $subtotal = 0;
                foreach ($data['products'] as $productData) {
                    $product = Product::find($productData['id']);
                    if (! $product) {
                        DB::rollBack();
                        return $this->errorResponse("Product not found: {$productData['id']}");
                    }
                    $subtotal += $product->price * $productData['quantity'];
                }

                [$deliveryCharge, $tax] = $this->calculateCharges($subtotal);
                $finalTotal = $this->calculateFinalTotal($subtotal, 0, $tax, $deliveryCharge);
            }
    
            $order = $user->orders()->create([
                'user_id' => $user->id,
                'branch_id' => $branch->id,
                'sub_total' => $subtotal,
                'total_price' => $finalTotal,
                'discount' => 0,
                'coupon_id' => null,
                'status' => 'pending',
                'payment_status' => $data['pay_with'] === 'points' ? 'paid' : 'pending',
                'tax' => $tax,
                'delivery_charge' => $deliveryCharge,
                'type' => $data['order_type'],
                'user_location_id' => $request->user_location_id ?? null,
                'user_payment_id' => $request->user_payment_id ?? null,
                'schedual_date' => $data['schedual_date'],
                'pay_with' => $data['pay_with'],
            ]);
    
            foreach ($data['products'] as $productData) {
                $product = Product::find($productData['id']);
                $orderItem = $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $productData['quantity'],
                    'total_price' => $product->price * $productData['quantity'],
                ]);
    
                if (!empty($productData['option_values'])) {
                    $orderItem->optionValues()->attach($productData['option_values']);
                }


                // $product->increment('total_sales', $productData['quantity']);
    
                $rank = $user->rank();
                $basePoints = $product->points * $productData['quantity'];
                $baseStars = $product->stars * $productData['quantity'];
    
                if ($rank) {
                    $pointsToAdd = $basePoints * $rank->points_increment;
                    $starsToAdd = $baseStars * $rank->stars_increment;
                } else {
                    $pointsToAdd = $basePoints;
                    $starsToAdd = $baseStars;
                }
    
               $stripeData = $this->stripeService->createPaymentIntent($user, $finalTotal, $request->payment_method, $order->id, $pointsToAdd,  'scheduled' );

                // $user->increment('total_points', $pointsToAdd);
                // $user->increment('total_stars', $starsToAdd);
    
                $order->update(['points_increase_user' => $pointsToAdd]);
            }
    
            DB::commit();
    
            $message = $data['pay_with'] === 'points' 
                ? "Order scheduled successfully with {$totalPointsCost} points" 
                : 'Order scheduled successfully';
    
            return $this->successResponse($message, [
                'order' => new OrderResource($order),
                'client_secret' => $stripeData['payment_intent']?->client_secret ?? null,
                'stripe_customer_id' => $user?->stripe_customer_id ?? null,
                'ephemeral_key' => $stripeData['ephemeral_key']?->secret ?? null,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to schedule order', 500, ['error' => $e->getMessage()]);
        }
    }


    public function readScheduledOrder($orderId)
    {
        $user = Auth::guard('user')->user();
        $order = $user->orders()
            ->where('id', $orderId)
            ->where('status', 'scheduled')
            ->first();

        if (! $order) {
            return $this->errorResponse('Scheduled order not found', 404);
        }

        return $this->successResponse('Scheduled order retrieved successfully', new OrderResource($order));
    }

    public function deleteScheduledOrder($orderId)
    {
        $user = Auth::guard('user')->user();
        $order = $user->orders()
            ->where('id', $orderId)
            ->where('status', 'scheduled')
            ->first();

        if (! $order) {
            return $this->errorResponse('Scheduled order not found', 404);
        }

        $order->delete();

        return $this->successResponse('Scheduled order deleted successfully', []);
    }


    public function getScheduledOrdersSummary(Request $request ,  $orderId)
    {
        $request->validate([
            'coupon_code' => 'nullable|string|exists:coupons,code',
        ]);
    
        $user = Auth::guard('user')->user();
    
        $order = $user->orders()
            ->where('id', $orderId)
            ->where('status', 'scheduled')
            ->where('payment_status', 'pending') 
            ->first();
    
        if (!$order || $order->items->isEmpty()) {
            return $this->errorResponse('No scheduled orders found', 422);
        }
    
        $itemTotal = $order->items->sum('total_price');
    
        $deliveryCharge = (float) SiteSetting::value('delivery_charge', 0);
        $taxPercentage = (float) SiteSetting::value('tax_percentage', 0);
        $freeDeliveryMinimum = (float) SiteSetting::value('free_delivery_minimum', 0);
    
        if ($itemTotal >= $freeDeliveryMinimum) {
            $deliveryCharge = 0;
        }
    
        $taxAmount = round(($itemTotal * $taxPercentage) / 100, 2);
        $discount = 0;
        $couponId = null;
    
        if ($request['coupon_code']) {
            $coupon = Coupon::where('code', $request->coupon_code)
                ->where('is_active', true)
                ->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->first();
    
            if (!$coupon) {
                return $this->errorResponse('Invalid or expired coupon');
            }
    
            if ($coupon->usage_limit && $coupon->times_used >= $coupon->usage_limit) {
                return $this->errorResponse('Coupon usage limit reached');
            }
    
            if ($coupon->discount_type === 'percent') {
                $discount = round(($itemTotal * $coupon->discount_value) / 100, 2);
            } elseif ($coupon->discount_type === 'fixed') {
                $discount = min($coupon->discount_value, $itemTotal);
            }
    
            $couponId = $coupon->id;
        }
    
        $total = round($itemTotal - $discount + $taxAmount + $deliveryCharge, 2);
    
        // Calculate total points cost
        $totalPointsCost = 0;
        foreach ($order->items as $orderItem) {
            if ($orderItem->product && !is_null($orderItem->product->price_with_points)) {
                $totalPointsCost += ($orderItem->product->price_with_points * $orderItem->quantity);
            }
        }
    
        $descriptionDelivery = 'Discount applies to selected items ordered with your added product';
    
        return $this->successResponse('Order summary', [
            'subtotal' => round($itemTotal, 2),
            'discount' => $discount,
            'delivery_charge' => $deliveryCharge,
            'tax' => $taxAmount,
            'total' => $total,
            'total_price_with_points' => $totalPointsCost,
            'user_total_points' => $user->total_points,
            'description_delivery' => $descriptionDelivery,
            'coupon_applied' => $request['coupon_code'] ? true : false,
            'branch' => $order->branch ? [
                'id' => $order->branch->id,
                'name' => $order->branch->name,
            ] : null,
            'items' => $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_name' => $item->product->name ?? null,
                    'quantity' => $item->quantity,
                    'total_price' => $item->total_price,
                ];
            }),
        ]);
    }

    public function checkout(Request $request, $orderId)
    {
        $request->validate([
            'coupon_code' => 'nullable|string|exists:coupons,code',
            'pay_with' => 'required|in:money,points',
        ]);
    
        $user = Auth::guard('user')->user();
        
        $order = $user->orders()
            ->where('status', 'scheduled')
            ->where('payment_status', 'pending')
            ->where('id', $orderId)
            ->first();
    
        if (!$order) {
            return $this->errorResponse('Scheduled order not found', 404);
        }

        //       // check Distance
        
        // $branch = Branch::findOrFail($order->branch_id);
        // $userLocation = $user->locations()->findOrFail($request->user_location_id);
        // $check = $branch->isInsideScope($userLocation->latitude, $userLocation->longitude);
        // if (!$check['inside']) {
        //     return $this->errorResponse("Sorry, you're outside branch delivery area", 400, [
        //         'distance'    => $check['distance'],
        //         'scope_work'  => $check['scope'],
        //     ]);
        // }
    
        DB::beginTransaction();
        try {
            // Handle payment with points
            if ($request['pay_with'] === 'points') {
                $totalPointsCost = $this->calculatePointsCostFromOrder($order);
                $this->validateAndDeductPoints($user, $totalPointsCost);
                
                // For points payment, no money charges apply
                $itemTotal = 0;
                $deliveryCharge = 0;
                $taxAmount = 0;
                $discount = 0;
                $couponId = null;
                $total = 0;
            } else {
                // Handle payment with money (existing logic)
                $itemTotal = $order->items->sum('total_price');
                
                $deliveryCharge = (float) SiteSetting::value('delivery_charge', 0);
                $taxPercentage = (float) SiteSetting::value('tax_percentage', 0);
                $freeDeliveryMinimum = (float) SiteSetting::value('free_delivery_minimum', 0);
        
                if ($itemTotal >= $freeDeliveryMinimum) {
                    $deliveryCharge = 0;
                }
        
                $taxAmount = round(($itemTotal * $taxPercentage) / 100, 2);
                $discount = 0;
                $couponId = null;
        
                if ($request['coupon_code']) {
                    $coupon = Coupon::where('code', $request->coupon_code)
                        ->where('is_active', true)
                        ->whereDate('start_date', '<=', now())
                        ->whereDate('end_date', '>=', now())
                        ->first();
        
                    if (!$coupon) {
                        DB::rollBack();
                        return $this->errorResponse('Invalid or expired coupon');
                    }
        
                    if ($coupon->usage_limit && $coupon->times_used >= $coupon->usage_limit) {
                        DB::rollBack();
                        return $this->errorResponse('Coupon usage limit reached');
                    }
        
                    if ($coupon->discount_type === 'percent') {
                        $discount = round(($itemTotal * $coupon->discount_value) / 100, 2);
                    } elseif ($coupon->discount_type === 'fixed') {
                        $discount = min($coupon->discount_value, $itemTotal);
                    }
        
                    $couponId = $coupon->id;
                    
                    $coupon->increment('times_used');
                }
        
                $total = round($itemTotal - $discount + $taxAmount + $deliveryCharge, 2);
            }
    
            $order->update([
                'payment_status' => 'paid',
                'discount' => $discount,
                'coupon_id' => $couponId,
                'tax' => $taxAmount,
                'delivery_charge' => $deliveryCharge,
                'total_price' => $total,
                'sub_total' => $itemTotal,
                'pay_with' => $request['pay_with'],
            ]);
    
            DB::commit();

            $message = $request['pay_with'] === 'points' 
                ? "Payment completed successfully with {$totalPointsCost} points" 
                : 'Payment completed successfully';
    
            return $this->successResponse($message, new OrderResource($order->fresh()));
            
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Payment failed', 500, ['error' => $e->getMessage()]);
        }
    }


    private function calculateCharges(float $subtotal): array
    {
        $deliveryCharge = (float) SiteSetting::value('delivery_charge', 0);
        $taxPercentage = (float) SiteSetting::value('tax_percentage', 0);
        $freeDeliveryMinimum = (float) SiteSetting::value('free_delivery_minimum', 0);

        if ($subtotal >= $freeDeliveryMinimum) {
            $deliveryCharge = 0;
        }

        $tax = round(($subtotal * $taxPercentage) / 100, 2);

        return [$deliveryCharge, $tax];
    }

    private function calculateFinalTotal(float $subtotal, float $discount, float $tax, float $deliveryCharge): float
    {
        return round($subtotal - $discount + $tax + $deliveryCharge, 2);
    }

    /**
     * Calculate the total points cost from products array
     */
    private function calculatePointsCostFromProducts(array $products): int
    {
        $totalPoints = 0;

        foreach ($products as $productData) {
            $product = Product::find($productData['id']);
            
            if (!$product || is_null($product->price_with_points)) {
                throw new \Exception("Product #{$productData['id']} does not support payment with points");
            }

            $totalPoints += ($product->price_with_points * $productData['quantity']);
        }

        return $totalPoints;
    }

    /**
     * Calculate the total points cost from order items
     */
    private function calculatePointsCostFromOrder($order): int
    {
        $totalPoints = 0;

        foreach ($order->items as $orderItem) {
            $product = $orderItem->product;
            
            if (!$product || is_null($product->price_with_points)) {
                throw new \Exception("Product #{$orderItem->product_id} does not support payment with points");
            }

            $totalPoints += ($product->price_with_points * $orderItem->quantity);
        }

        return $totalPoints;
    }

    /**
     * Validate user has sufficient points and deduct them
     */
    private function validateAndDeductPoints($user, int $requiredPoints): void
    {
        if ($user->total_points < $requiredPoints) {
            throw new \Exception("Insufficient points. You have {$user->total_points} points, but need {$requiredPoints} points");
        }

        $user->decrement('total_points', $requiredPoints);
    }
}
