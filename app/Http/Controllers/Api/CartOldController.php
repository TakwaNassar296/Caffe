<?php

namespace App\Http\Controllers\Api;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Api\CartResource;
use App\Http\Resources\CartItemResource;
use App\Http\Requests\Api\Cart\StoreCartitems;

class CartOldController extends Controller
{
    use ApiResponse;
    public function getCart()
    {
        $user = Auth::guard('user')->user();

        if (!$user->cart) {
            return $this->successResponse('Cart IS empty');
        }

        if ($user->cart->items->isEmpty()) {
            return $this->successResponse('Cart is empty', new CartResource($user->cart));
        }
        
        $cart = $user->cart()->with(['items.product'])->first();

        return $this->successResponse('Cart fetched successfully', new CartResource($cart));
    }

    public function addItem(StoreCartitems $request)
    {
        $user = Auth::guard('user')->user();

        $cart = $user->cart ?? $user->cart()->create();

        $existingItem = $cart->items()->where('product_id', $request['product_id'])->first();

        if ($existingItem) {
            $existingItem->update([
                'quantity' => $existingItem->quantity + ($request['quantity'] ?? 1),
            ]);

            return $this->successResponse('Quantity updated to ' . $existingItem->quantity, new CartResource($cart));
        }

        $item = $cart->items()->create([
            'product_id' => $request['product_id'],
            'quantity'   => $request['quantity'] ?? 1,
        ]);

        $cart = $user->cart()->with(['items.product'])->first();
        return $this->successResponse('Cart updated successfully', new CartResource($cart));
    }

    public function removeItem($itemId)
    {
        $user = Auth::guard('user')->user();

        $cart = $user->cart;

        if (!$cart) {
            return $this->errorResponse('Cart not found', 404);
        }

        $item = $cart->items()->where('id', $itemId)->first();

        if (!$item) {
            return $this->errorResponse('Item not found in cart', 404);
        }

        $item->delete();

        $cart = $user->cart()->with('items.product')->first();

        return $this->successResponse('Item removed from cart', new CartResource($cart));
    }

    public function decreaseItemQuantity($id)
    {
        $user = Auth::guard('user')->user();

        $cart = $user->cart;
        if (!$cart) {
            return $this->errorResponse('Cart not found', 404);
        }
        if ($cart->items->isEmpty()) {
            return $this->errorResponse('Cart is empty', 404);
        }

        $item = $cart?->items()->where('id', $id)->first();

        if (!$item) {
            return $this->errorResponse('Item not found', 404);
        }

        if ($item->quantity > 1) {
            $item->decrement('quantity', 1);
        } else {
            $item->delete();
        }
        $cart = $user->cart()->with('items.product')->first();

        return $this->successResponse('Cart updated successfully', new CartResource($cart));
    }

    public function clearCart()
    {
        $user = Auth::guard('user')->user();
        $cart = $user->cart;

        if (!$cart) {
            return $this->errorResponse('Cart not found', 404);
        }

        $cart->items()->delete();

        return $this->successResponse('Cart cleared');
    }
}
