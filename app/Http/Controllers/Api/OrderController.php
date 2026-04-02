<?php

namespace App\Http\Controllers\Api;



use App\Models\Cart;
use App\Models\Order;
use App\Models\Branch;
use App\Models\Coupon;
use App\Models\Product;
use NotificationDriver;
use App\Traits\GeoTrait;
use App\Models\SiteSetting;
use App\Traits\ApiResponse;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Api\OrderResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use App\Http\Requests\Api\Order\CheckoutRequest;

class OrderController extends Controller
{
    use ApiResponse, GeoTrait;

    public function __construct(protected StripeService $stripeService) {}

    public function getOrderSummary(Request $request)
    {
        $request->validate([
            'coupon_code' => 'nullable|string|exists:coupons,code',
        ]);

        $user = Auth::guard('user')->user();

        $cart = $user->cart()->with('items')->first();

        if (! $cart || $cart->items->isEmpty()) {
            return $this->errorResponse(__('apis.cart_empty'), 422);
        }

        // check Distance
        // $branch = $cart->branch;
        // $userLocation = $user->locations()->findOrFail($request->user_location_id);
        // $check = $branch->isInsideScope($userLocation->latitude, $userLocation->longitude);
        // if (!$check['inside']) {
        //     return $this->errorResponse(__('apis.branch_delivery_area'), 400, [
        //         'distance'    => $check['distance'],
        //         'scope_work'  => $check['scope'],
        //     ]);
        // }

        $itemTotal = $cart->items->sum('total_price');

        $deliveryCharge = (float) SiteSetting::value('delivery_charge', 0);
        $taxPercentage = (float) SiteSetting::value('tax_percentage', 0);
        $freeDeliveryMinimum = (float) SiteSetting::value('free_delivery_minimum', 0);

        if ($itemTotal >= $freeDeliveryMinimum) {
            $deliveryCharge = 0;
        }

        $taxAmount = round(($itemTotal * $taxPercentage) / 100, 2);
        $discount = 0;
        $couponData = null;

        if ($request['coupon_code']) {
            $coupon = Coupon::where('code', $request->coupon_code)
                ->where('is_active', true)
                ->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->first();
            if (! $coupon) {
                return $this->errorResponse(__('apis.invalid_coupon'));
            }

            if ($coupon->discount_type === 'percent') {
                $discount = round(($itemTotal * $coupon->discount_value) / 100, 2);
            } elseif ($coupon->discount_type === 'fixed') {
                $discount = min($coupon->discount_value, $itemTotal);
            }
        }

        $total = round($itemTotal - $discount + $taxAmount + $deliveryCharge, 2);

        // Calculate total points cost
        $totalPointsCost = 0;
        foreach ($cart->items as $cartItem) {
            if ($cartItem->product && ! is_null($cartItem->product->price_with_points)) {
                $totalPointsCost += ($cartItem->product->price_with_points * $cartItem->quantity);
            }
        }

        $descriptionDelivery = SiteSetting::value('text_order', "Discount applies to selected items ordered with your added product");
        return $this->successResponse(__('apis.order_summary'), [
            'subtotal' => round($itemTotal, 2),
            'discount' => $discount,
            'delivery_charge' => $deliveryCharge,
            'tax' => $taxAmount,
            'total' => $total,
            'total_price_with_points' => $totalPointsCost,
            'user_total_pints' =>  $user->total_points,
            'description_delivery' => $descriptionDelivery,


        ]);
    }

    /**
     * create order - check coupon - get user cart  - calc charage - calc order - clear cart
     */
    public function checkout(CheckoutRequest $request)
    {
        $user = Auth::guard('user')->user();
        $cart = $this->getUserCart($user);

        if (! $cart || $cart->items->isEmpty()) {
            return $this->errorResponse(__('apis.cart_empty'), 400);
        }

        DB::beginTransaction();

        try {

            // Handle payment with points
              $totalPointsCost = $this->calculatePointsCost($cart);
            if ($request['pay_with'] === 'points') {
                $totalPointsCost = $this->calculatePointsCost($cart);
                $this->validateAndDeductPoints($user, $totalPointsCost);

                 $subtotal = 0;
                $discount = 0;
                $couponId = null;
                $deliveryCharge = 0;
                $tax = 0;
                $finalTotal = 0;
            } else {
                // Handle payment with money (existing logic)
                [$subtotal, $discount, $couponId] = $this->applyCoupon($request['coupon_code'], $cart);
                [$deliveryCharge, $tax] = $this->calculateCharges($subtotal);
                $finalTotal = $this->calculateFinalTotal($subtotal, $discount, $tax, $deliveryCharge);
            }

            if ($request['pay_with'] === 'points') {
                $user->total_points = $user->total_points - $totalPointsCost;
                $user->save();
            }
            $order = $this->createOrder($user, $cart, $request, $finalTotal, $discount, $couponId, $tax, $deliveryCharge, $subtotal);

            $this->processOrderItems($order, $cart, $user);

            $this->clearCart($cart);

            $drivers = $order->branch->drivers;


            // Notification::send($drivers, new NotificationDriver($order, "New order received! {$order->id}"));


            if ($request['pay_with'] === 'money') {

               
                $stripeData    = $this->stripeService->createPaymentIntent(
                    $user,
                    $finalTotal,
                    $request->payment_method,
                    $order->id,
                    $totalPointsCost,
                );
                $paymentIntent = $stripeData['payment_intent'];
                $ephemeralKey  = $stripeData['ephemeral_key'];
            }


            DB::commit();

            $message = $request['pay_with'] === 'points'
                ? __('apis.order_with_points', ['points' => $totalPointsCost])
                : __('apis.order_created');

            return $this->successResponse($message, [
                'order' => new OrderResource($order),
                'client_secret' => $paymentIntent?->client_secret ?? null,
                'stripe_customer_id' => $user?->stripe_customer_id ?? null,
                'ephemeral_key' => $ephemeralKey?->secret ?? null,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse($e->getMessage());
        }
    }

    private function getUserCart($user)
    {
        return $user->cart()->with('items.optionValues', 'items.product')->first();
    }

    private function applyCoupon(?string $couponCode, $cart): array
    {
        $subtotal = $cart->items->sum('total_price');
        $discount = 0;
        $couponId = null;

        if ($couponCode) {
            $coupon = Coupon::where('code', $couponCode)->first();

            if (! $coupon || ! $coupon->isValid()) {
                throw new \Exception('Invalid or expired coupon');
            }

            $couponId = $coupon->id;
            $discount = $coupon->calculateDiscount($subtotal);

            $coupon->increment('used');
        }

        return [$subtotal, $discount, $couponId];
    }

    private function createOrder($user, $cart, $request, $finalTotal, $discount, $couponId, $tax, $deliveryCharge, $subtotal)
    {
       // $driverFinance = SiteSetting::first()->driver_finance ?? 20;

        $driverFinance = ($request['type'] === 'pick_up') 
            ? 0 
            : (SiteSetting::first()->driver_finance ?? 20);

        return $user->orders()->create([
            'user_id' => $user->id,
            'sub_total' => $subtotal,
            'total_price' => $finalTotal,
            'discount' => $discount,
            'coupon_id' => $couponId,
            'status' => 'pending',
            'branch_id' => $cart->branch_id,
            'user_location_id' => $request->input('user_location_id', null),
            'user_payment_id' => $request['user_payment_id'] ?? 1,
            'tax' => $tax,
            'delivery_charge' => $deliveryCharge,
            'type' => $request['type'],
            'pay_with' => $request['pay_with'],
            'driver_finance' => $driverFinance,
            'payment_status' => 'paid',
        ]);
    }

    private function processOrderItems($order, $cart, $user)
    {
        $rank = $user->rank();

        foreach ($cart->items as $cartItem) {
            $orderItem = $order->items()->create([
                'product_id' => $cartItem->product_id,
                'quantity' => $cartItem->quantity,
                'total_price' => $cartItem->total_price,
            ]);

            if ($cartItem->optionValues && $cartItem->optionValues->isNotEmpty()) {
                foreach ($cartItem->optionValues as $value) {
                    $orderItem->optionValues()->attach($value->pivot->product_value_id);
                }
            }

            $product = $cartItem->product;
            $product->increment('total_sales', $cartItem->quantity);

            $basePoints = $product->points * $cartItem->quantity;
            $baseStars = $product->stars * $cartItem->quantity;

            if ($rank) {
                $pointsToAdd = $basePoints * $rank->points_increment;
                $starsToAdd = $baseStars * $rank->stars_increment;
            } else {
                $pointsToAdd = $basePoints;
                $starsToAdd = $baseStars;
            }

            $order->points_increase_user += $pointsToAdd;
            $order->save();

            $user->increment('total_points', $pointsToAdd);
            $user->increment('total_stars', $starsToAdd);
        }
    }

    private function clearCart($cart)
    {
        $cart->items()->delete();
        $cart->delete();
    }

    public function reorder(Request $request, $orderId)
    {
        $user = Auth::guard('user')->user();
        $confirm = $request->boolean('confirm');


        if ($user->cart && $user->cart->items()->exists()) {
            return $this->errorResponse(__('apis.cart_not_empty'), 400);
        }

        $oldOrder = Order::with(['items.optionValues', 'items.product'])
            ->where('user_id', $user->id)
            ->where('id', $orderId)
            ->first();

        if (! $oldOrder) {
            return $this->errorResponse(__('apis.order_not_found'), 404);
        }


        $branch = Branch::find($request->branch_id);
        if (! $branch) {
            return $this->errorResponse(__('apis.branch_not_found'), 404);
        }


        $unavailableItems = [];

        foreach ($oldOrder->items as $oldItem) {
            $product = $oldItem->product;



            if (! $product || (int) $product->is_active !== 1) {

                $unavailableItems[] = [
                    'product_id' => $oldItem->product_id,
                    'name' => $product->name ?? null,
                ];
                continue;
            }

            $isAvailable = DB::table('branch_product')
                ->where('branch_id', $branch->id)
                ->where('product_id', $product->id)
                ->where('status', 1)
                ->exists();


            if (! $isAvailable) {
                $unavailableItems[] = [
                    'product_id' => $product->id,
                    'name' => $product->name ?? null,
                ];
            }
        }

        if (! $confirm && ! empty($unavailableItems)) {


            return $this->customResponse(
                key: 'need_confirmation',
                message: __('apis.reorder_unavailable_items'),
                status: 409,
                data: [
                    'confirm_required' => true,
                    'unavailable_items' => $unavailableItems,
                ]
            );
        }


        DB::beginTransaction();

        try {
            $cart = Cart::firstOrCreate([
                'user_id' => $user->id,
                'branch_id' => $oldOrder->branch_id,
            ]);



            $skippedItems = [];
            $addedCount = 0;

            foreach ($oldOrder->items as $oldItem) {
                $product = $oldItem->product;


                if (! $product) {

                    $skippedItems[] = [
                        'product_id' => $oldItem->product_id,
                        'reason' => 'PRODUCT_NOT_FOUND',
                    ];
                    continue;
                }

                if ((int) $product->is_active !== 1) {

                    $skippedItems[] = [
                        'product_id' => $product->id,
                        'reason' => 'PRODUCT_INACTIVE',
                    ];
                    continue;
                }

                $isAvailable = DB::table('branch_product')
                    ->where('branch_id', $branch->id)
                    ->where('product_id', $product->id)
                    ->where('status', 1)
                    ->exists();


                if (! $isAvailable) {

                    $skippedItems[] = [
                        'product_id' => $product->id,
                        'reason' => 'NOT_AVAILABLE_IN_BRANCH',
                    ];
                    continue;
                }

                $cartItem = $cart->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $oldItem->quantity,
                    'original_price' => $product->price,
                    'discount_price' => 0,
                    'total_price' => $product->price * $oldItem->quantity,
                ]);

                $addedCount++;

                if ($oldItem->optionValues && $oldItem->optionValues->isNotEmpty()) {
                    $cartItem->optionValues()->attach(
                        $oldItem->optionValues->pluck('id')->toArray()
                    );
                }
            }


            if ($addedCount === 0) {
                DB::rollBack();
                return $this->errorResponse(__('apis.no_items_reorder'), 422);
            }

            DB::commit();


            return $this->successResponse(__('apis.items_added_cart'), [
                'cart_id' => $cart->id,
                'items_count' => $addedCount,
                'skipped_items' => $skippedItems,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();


            return $this->errorResponse(__('apis.failed_reorder'), 500, [
                'error' => $e->getMessage(),
            ]);
        }
    }


    public function cancelOrder(Request $request, $orderId)
    {
        $user = Auth::guard('user')->user();

        $order = Order::where('id', $orderId)
            ->where('user_id', $user->id)
            ->first();

        if (! $order) {
            return $this->errorResponse(__('apis.order_not_found'), 404);
        }

        if (in_array($order->status, ['complete'])) {
            return $this->errorResponse(__('apis.invalid_order_status'), 422);
        }

        $order->status = 'canceled';
        $order->save();

        // استرجاع النقاط والنجوم
        // foreach ($order->items as $item) {
        //     $product = $item->product;
        //     if ($product) {
        //         $user->decrement('total_points', $product->points * $item->quantity);
        //         $user->decrement('total_stars', $product->stars * $item->quantity);
        //     }
        // }
        if ($order->driver) {
            // Notification::send($order->driver, new NotificationDriver($order, "Order canceled! {$order->id}"));
        }

        return $this->successResponse(__('apis.order_canceled_success'), []);
    }

    private function calculateCharges(float $subtotal , string $type = null): array
    {
        $deliveryCharge = (float) SiteSetting::value('delivery_charge', 0);
        $taxPercentage = (float) SiteSetting::value('tax_percentage', 0);
        $freeDeliveryMinimum = (float) SiteSetting::value('free_delivery_minimum', 0);

        if ($type === 'pick_up' || $subtotal >= $freeDeliveryMinimum) {
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
     * Calculate the total points cost from cart items
     */
    private function calculatePointsCost($cart): int
    {
        $totalPoints = 0;

        foreach ($cart->items as $cartItem) {
            $product = $cartItem->product;

            if (! $product || is_null($product->price_with_points)) {
                throw new \Exception(__('apis.no_points_support', ['id' => $cartItem->product_id]));
            }

            $totalPoints += ($product->price_with_points * $cartItem->quantity);
        }

        return $totalPoints;
    }

    /**
     * Validate user has sufficient points and deduct them
     */
    private function validateAndDeductPoints($user, int $requiredPoints): void
    {
        if ($user->total_points < $requiredPoints) {
            throw new \Exception(__('apis.insufficient_points', ['user_points' => $user->total_points, 'required_points' => $requiredPoints]));
        }

        $user->decrement('total_points', $requiredPoints);
    }


    public function verify(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $order = Order::where('qr_token', $request->token)->first();

        if (! $order) {
            return $this->errorResponse(__('apis.invalid_qr'), 400);
        }

        // if ($order->user_id !== auth('user')->id()) {
        //     return $this->errorResponse(__('apis.order_not_assigned'), 403);
        // }

        if ($order->status === 'completed' || $order->status === 'cancelled') {
            return $this->errorResponse(__('apis.order_already_completed'), 400);
        }

        $order->update([
            'status'      => 'completed',
            // 'verified_at' => now(), // 
        ]);

        return $this->successResponse(__('apis.order_completed'), [
            'order_id' => $order->id,
            'user_id'  => $order->user_id,
        ]);
    }

    // TODO : Make it in another controller 
    public function stripePaymentWebhook(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = $this->stripeService->constructWebhookEvent($payload, $sigHeader);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid webhook'], 400);
        }

        match ($event->type) {
            'payment_intent.succeeded'      => $this->stripeService->handlePaymentSucceeded($event),
            'payment_intent.payment_failed' => $this->stripeService->handlePaymentFailed($event),
            default                         => null,
        };

        return $this->successResponse(__('apis.payment_success'), []);
    }


    public function stripeAttachPaymentMethod(Request $request)
    {
        $user = auth('user')->user();

        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first());
        }

        $this->stripeService->attachPaymentMethod($user, $request->payment_method);

        return $this->successResponse(__('apis.payment_method_attached'), []);
    }


    public function stripShowPaymentMethod(Request $request, $paymentMethodId)
    {
        $user = auth('user')->user();

        if (! $user->stripe_customer_id) {
            return $this->errorResponse(__('apis.payment_method_not_found'));
        }

        $paymentMethod = $this->stripeService->retrievePaymentMethod($user, $paymentMethodId);

        return $this->successResponse(__('apis.payment_method_retrieved'), $paymentMethod);
    }

    public function stripGetPaymentMethod(Request $request)
    {
        $user = auth('user')->user();

        $paymentMethods = $this->stripeService->listPaymentMethods($user);

        return $this->successResponse(__('apis.payment_methods'), $paymentMethods);
    }



    public function stripDetachPaymentMethod(Request $request)
    {
        $user = auth('user')->user();

        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first());
        }

        $this->stripeService->detachPaymentMethod($request->payment_method);

        return $this->successResponse(__('apis.payment_method_detached'), []);
    }

    public function buyPoints(Request $request)
    {
        $user = auth('user')->user();

        $validator = Validator::make($request->all(), [
            'points' => 'required|numeric|min:1',
            'payment_method' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first());
        }

        $points = $request->points;

        $amountSalry = $points * SiteSetting::value('money_per_point', 1);

        $stripeData = $this->stripeService->charge($user, $amountSalry, $points, $request->payment_method);

        return $this->successResponse(__('apis.points_purchased'), [
            'client_secret'      => $stripeData['payment_intent']?->client_secret ?? null,
            'stripe_customer_id' => $stripeData['stripe_customer_id'] ?? null,
            'ephemeral_key'      => $stripeData['ephemeral_key']?->secret ?? null,
        ]);
    }
}
