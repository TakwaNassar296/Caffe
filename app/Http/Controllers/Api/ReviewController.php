<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\Review;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ProductReviewRequest;
use App\Http\Requests\Api\StoreReviewRequest;

class ReviewController extends Controller
{
    use ApiResponse;

    public function driverReview(StoreReviewRequest $request, $orderId)
    {
        return $this->createReview($request, 'driver', $orderId);
    }

    public function branchReview(StoreReviewRequest $request, $orderId)
    {
        return $this->createReview($request, 'branch', $orderId);
    }

    public function productReview(ProductReviewRequest $request, $orderId) 
    {
        $user = auth()->guard('user')->user();
        $data = $request->validated();

        $order = Order::where('id', $orderId)
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->first();

        if (!$order) {
            return $this->errorResponse('order not found or not completed', 404);
        }

        foreach ($data['products'] as $product) {
            $exists = Review::where('order_id', $orderId)
                ->where('reviewable_type', \App\Models\Product::class)
                ->where('reviewable_id', $product['id'])
                ->exists();

            if (!$exists) {
                Review::create([
                    'order_id'        => $orderId,
                    'user_id'         => $user->id,
                    'reviewable_type' => \App\Models\Product::class,
                    'reviewable_id'   => $product['id'],
                    'rating'          => $product['rating'],
                    'comment'         => $product['comment'] ?? null,
                    'order_id'        => $orderId,
                ]);
            }
        }

        return $this->successResponse('review created successfully');
    }

    private function createReview(StoreReviewRequest $request, string $type, $orderId)
    {
        $user = auth()->guard('user')->user();
        $data = $request->validated();

           $order = Order::where('id', $orderId)
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->first();

        if (!$order) {
            return $this->errorResponse( 'order not found or not completed', 404);
        }

        $exists = Review::where('order_id', $orderId)
            ->where('reviewable_type', $this->getModelClass($type))
            ->where('reviewable_id', $data['reviewable_id'])
            ->exists();

        if ($exists) {
            return $this->errorResponse('already_reviewed', 422);
        }

        Review::create([
            'order_id'        => $orderId,
            'user_id'         => $user->id,
            'reviewable_type' => $this->getModelClass($type),
            'reviewable_id'   => $data['reviewable_id'],
            'rating'          => $data['rating'],
            'comment'         => $data['comment'] ?? null,
        ]);

        return $this->successResponse('review created successfully');
    }

    private function getModelClass(string $type): string
    {
        return match ($type) {
            'driver'  => \App\Models\Driver::class,
            'branch'  => \App\Models\Branch::class,
            'product' => \App\Models\Product::class,
            default   => abort(400, __('apis.invalid_review_type')),
        };
    }


    public function completeOrder($orderId)
    {
        $user = auth()->guard('user')->user();

        $order = Order::where('id', $orderId)
            ->where('user_id', $user->id)
            ->first();

        if (!$order) {
            return $this->errorResponse('order not found or not completed', 404);
        }

        $order->status = 'completed';

        $order->save();

        return $this->successResponse('Order marked as completed successfully');
    }
}