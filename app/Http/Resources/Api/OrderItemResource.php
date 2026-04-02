<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'product_id'    => $this->product_id ?? null,
            'product_name'  => $this->product?->name ?? null,
            'description'   => $this->product?->description ?? null,
            'image' => !empty($this->product?->image[0])
                ? asset('storage/' . $this->product->image[0])
                : asset('images/default.png'),
            'price'         => $this->product?->price ?? null,
            'price_after_coupon' => $this->product?->calcPrice() ?? null,
            'quantity'           => (int) ($this->quantity ?? 1),
            'points'        => $this->product?->points ?? null,
            'stars'         => $this->product?->stars ?? null,
            'rating_avg'    => $this->product?->averageRating() ?? null,
            'revies_count'  => $this->product?->reviewsCount() ?? null,
            'delivery_time' => $this->product?->delivery_time ?? null,
            'total_sales'   => $this->product?->total_sales ?? null,
            'total_price'   => $this->total_price ?? null,
            'options'       => $this->optionValues->map(function ($optionValue) {
                return [
                    'id' => $optionValue->id ?? null,
                    'value' => $optionValue->value ?? null,
                    'extra_price' => $optionValue->extra_price ?? null,
                ];
            }),
        ];
    }
}
