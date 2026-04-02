<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\CartItem;
use App\Models\SiteSetting;
use App\Traits\ApiResponse;
use App\Models\ProductValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CartItemOptionValue;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Api\CartResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Api\Cart\AddToCartRequest;

class CartController extends Controller
{
    use ApiResponse;
    public function index()
    {
        $user = Auth::guard('user')->user();

        $cart = $user->cart()->with('items.optionValues', 'items.product')->first();

        if (!$cart || $cart->items->isEmpty()) {
            return $this->successResponse(__('apis.cart_empty'), []);
        }

        return $this->successResponse(__('apis.cart_retrieved'), new CartResource($cart));
    }

    public function addToCart(AddToCartRequest $request)
    {
        $user = Auth::guard('user')->user();

        // 1. Validate option values actually belong to this product
        $validOptionValues = ProductValue::whereIn('id', $request['option_value_ids'])
            ->whereHas('productOption', function ($query) use ($request) {
                $query->where('product_id', $request['product_id']);
            })->pluck('id')->toArray();

        if (count($validOptionValues) !== count($request['option_value_ids'])) {
            return $this->errorResponse(__('apis.invalid_option_values'), 422);
        }

        // 2. Check the product is available in this branch
        $branchProduct = DB::table('branch_product')
            ->where('product_id', $request['product_id'])
            ->where('branch_id', $request['branch_id'])
            ->where('status', true)
            ->first();

        if (!$branchProduct) {
            return $this->errorResponse(__('apis.product_not_available'), 422);
        }

        DB::beginTransaction();
        try {
            // Retrieve active cart (if exists)
            $cart = $user->cart()->first();

            if ($cart) {
                // Strictly compare branch IDs (cast to int if needed to avoid type mismatch)
                if ((int)$cart->branch_id !== (int)$request['branch_id']) {
                    DB::rollBack();
                    return $this->errorResponse(__('apis.same_branch_only'), 422);
                }
            } else {
                // Create a new cart tied to this branch
                $cart = $user->cart()->create([
                    'branch_id' => $request['branch_id'],
                ]);
            }

            $product = Product::findOrFail($request['product_id']);
            $optionValues = ProductValue::whereIn('id', $request['option_value_ids'])->get();

            // Calculate the price
            $original_price = ($product->calcPrice() + $optionValues->sum('extra_price')) * $request['quantity'];
            $discount_price = 0;

            // Get discount for related products
            $siteSetting = SiteSetting::first();
            $relatedDiscount = $siteSetting ? $siteSetting->related_product_discount : 25;
            $relatedProducts = $product->relatedProducts()->pluck('products.id')->toArray();

            // Check if user has any related product already in cart
            $hasRelatedInCart = $cart->items()
                ->whereIn('product_id', $relatedProducts)
                ->exists();

            if ($hasRelatedInCart && $relatedDiscount > 0) {
                $discount_price = $relatedDiscount * $request['quantity'];
            }

            $total_price = max($original_price - $discount_price, 0);

            // Create the cart item
            $cartItem = $cart->items()->create([
                'product_id'     => $request['product_id'],
                'quantity'       => $request['quantity'],
                'original_price' => $original_price,
                'discount_price' => $discount_price,
                'total_price'    => $total_price,
            ]);

            // Attach option values to cart item
            $optionValueCreateData = [];
            foreach ($validOptionValues as $valueId) {
                $optionValueCreateData[] = [
                    'cart_item_id'     => $cartItem->id,
                    'product_value_id' => $valueId,
                ];
            }
            // Bulk insert for efficiency
            if ($optionValueCreateData) {
                CartItemOptionValue::insert($optionValueCreateData);
            }

            DB::commit();

            return $this->successResponse(__('apis.item_added'));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse(__('apis.something_wrong') . ': ' . $e->getMessage(), 500);
        }
    }


    public function updateQuantity(Request $request, $itemId)
    {
        $validation = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validation->fails()) {
            return $this->errorResponse($validation->errors()->first(), 422);
        }

        $user = Auth::guard('user')->user();

        $cartItem = CartItem::where('id', $itemId)
            ->whereHas('cart', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->first();

        if (!$cartItem) {
            return $this->errorResponse(__('apis.cart_item_not_found'), 404);
        }

        $product = $cartItem->product;
        $optionValues = $cartItem->optionValues;
        $total_price = $product->calcPrice() + $optionValues->sum('extra_price');
        $total_price *= $request->quantity;

        $cartItem->update([
            'quantity' => $request->quantity,
            'total_price' => $total_price,
        ]);

        $cart = $user->cart()->with('items')->first();
        $itemTotal = $cart->items->sum('total_price');

        $deliveryCharge = (float) SiteSetting::value('delivery_charge', 0);
        $taxPercentage = (float) SiteSetting::value('tax_percentage', 0);
        $freeDeliveryMinimum = (float) SiteSetting::value('free_delivery_minimum', 0);

        if ($itemTotal >= $freeDeliveryMinimum) {
            $deliveryCharge = 0;
        }

        $taxAmount = round(($itemTotal * $taxPercentage) / 100, 2);
        $discount = 0;

        $total = round($itemTotal - $discount + $taxAmount + $deliveryCharge, 2);



        $descriptionDelivery =  SiteSetting::value('text_cart', "Discount applies to selected items ordered with your added product");
        return $this->successResponse(__('apis.quantity_updated'), [
            'subtotal' => round($itemTotal, 2),
            'discount' => $discount,
            'delivery_charge' => $deliveryCharge,
            'tax' => $taxAmount,
            'total' => $total,
            'description_text' => $descriptionDelivery,
        ]);
    }


    public function removeItem(Request $request, $itemId)
    {
        $user = Auth::guard('user')->user();

        $item = CartItem::where('id', $itemId)
            ->whereHas('cart', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->first();

        if (!$item) {
            return $this->errorResponse(__('apis.cart_item_not_found'), 404);
        }

        $cart = $item->cart;

        $item->delete();

        if ($cart->items()->count() === 0) {
            $cart->delete();
            return $this->successResponse(__('apis.item_removed_cart_deleted'), []);
        }

        $cart = $user->cart()->with('items.optionValues', 'items.product')->first();
        return $this->successResponse(__('apis.item_removed'), new CartResource($cart));
    }

    public function clearCart()
    {
        $user = Auth::guard('user')->user();
        $cart = $user->cart;

        if (!$cart) {
            return $this->successResponse(__('apis.cart_already_empty'), []);
        }

        // foreach ($cart->items as $item) {
        //     $item->optionValues()->detach();
        //     $item->delete();
        // }

        $cart->items()->delete();
        $cart->delete();

        return $this->successResponse(__('apis.cart_cleared'), []);
    }
}
