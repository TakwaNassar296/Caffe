<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SingleProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $coupon = $this->coupon;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->image ? url("storage/{$this->image}") : null,
            'title' => $this->title,
            'rating' => $this->rating,
            'total_rating' => $this->total_rating,
            'price' => $this->price,
            'price_after_coupon' => $this->calcPrice(),
            'delivery_time' => $this->delivery_time,
            'points' => $this->points,
            'stars' => $this->stars,
            'amount' => $this->amount,
            'total_sales' => $this->total_sales,
            'coupon' => $this->coupon ? new CouponResource($this->coupon) : null,
            'category' => $this->coupon ? new CategoryResource($this->category) : null,

        ];
    }
}
