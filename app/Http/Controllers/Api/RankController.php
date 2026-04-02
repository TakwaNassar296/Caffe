<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\RankResource;
use App\Models\Rank;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;

class RankController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $ranks = Rank::all();
        if ($ranks->isEmpty()) {
            return $this->successResponse('Ranks is Empty');
        }

        return $this->successResponse('Rank names fetched successfully.', RankResource::collection($ranks), 200);
    }

    public function show($id)
    {
        $rank = Rank::find($id);

        if (! $rank) {
            return $this->errorResponse('Unauthenticated', 401);
        }

        return $this->successResponse('Rank details fetched.', new RankResource($rank));
    }

    public function userRankStats()
    {
        $user = Auth::guard('user')->user();
        $currentStars = $user->total_stars ?? 0;

        $currentRank = Rank::where('min_stars', '<=', $currentStars)
            ->where('max_stars', '>=', $currentStars)
            ->first();

        $nextRank = Rank::where('min_stars', '>', $currentStars)
            ->orderBy('max_stars')
            ->first();

        $isLastRank = ! $nextRank;

        $progress = $this->calculateProgress($currentRank, $currentStars);

        $orders = $this->getCompletedOrdersDetails($user);

        return $this->successResponse('User rank stats fetched.', [
            'current_rank' => $currentRank ? new RankResource($currentRank) : null,
            'next_rank' => $nextRank ? new RankResource($nextRank) : null,
            'is_last_rank' => $isLastRank,
            'user_stars' => $currentStars,
            'current_rank_name' => $user->rank_name,
            'progress_percent' => $progress,
            'orders' => $orders,
        ]);
    }

    private function calculateProgress($currentRank, $currentStars)
    {
        if (! $currentRank) {
            return 0;
        }

        $range = $currentRank->max_points - $currentRank->min_points;
        $earned = $currentStars - $currentRank->min_points;

        return $range > 0 ? round(($earned / $range) * 100, 1) : 100;
    }

    private function getCompletedOrdersDetails($user)
    {
        return $user->orders()
            ->where('status', 'completed')
            ->with(['items.product'])
            ->get()
            ->map(function ($order) {
                $firstItem = $order->items->first();
                $product = optional($firstItem)->product;

                return [
                    'order_id' => $order->id,
                    'created_at' => $order->created_at->format('Y-m-d'),
                    'product_name' => $product->name ?? '',
                    'item' => $firstItem ? [
                        'product_name' => $product->name ?? '',
                        'quantity' => $firstItem->quantity,
                        'stars_total' => ($product->stars ?? 0) * $firstItem->quantity,
                    ] : null,
                ];
            });
    }





    public function userPoints(){
        $user = Auth::guard('user')->user();
      
        $orders = $this->getCompletedOrdersDetails($user);

        return $this->successResponse('User points fetched.', [
            'points' => $user->total_points,
            'orders' => $orders
        ]);
    }
}
