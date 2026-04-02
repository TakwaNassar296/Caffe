<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\Api\ProductOtionResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\ProductOptionResource;

class ProductDetailsResourse extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isFavourite = false;
        $favouriteId = 0; // الافتراضي صفر بدل null

        if (auth('user')->check()) {
            $favourite = $this->favourites()
                ->where('user_id', auth('user')->id())
                ->when($request->has('branch_id'), function ($query) use ($request) {
                    $query->where('branch_id', $request->branch_id);
                })
                ->first();

            if ($favourite) {
                $isFavourite = true;
                $favouriteId = $favourite->id;
            }
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'images' => is_array($this->image)
                ? array_map(fn($img) => url("storage/{$img}"), $this->image)
                : [],
            'title' => $this->title,
            'description' => $this->description,
            'price' => (int) $this->price,
            'price_after_coupon' => $this->calcPrice(),
            'points' => $this->points,
            'stars' => $this->stars,
            'rating_avg' => $this->averageRating(),
            'revies_count' => $this->reviewsCount(),
            // 'delivery_time' => $this->delivery_time,
            'delivery_time' => $this->delivery_product_time,
            'stat_minutes' => $this->stat_minutes,
            'end_minutes' => $this->end_minutes,
            'total_sales' => $this->total_sales,
            'options' => $this->options  ? ProductOptionResource::collection($this->options) : null,
            'remaining_quantity' => $this->remaining_quantity,
            'is_favourite' => $isFavourite,
            'favourite_id' => $favouriteId,
            'category' => new CategoryResource($this->category),
        ];
    }
}
