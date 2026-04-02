<?php

namespace App\Http\Resources\Branch;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductBranchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $branchProduct = $this->branchProduct ?? null;
        $amount = $branchProduct ? ($branchProduct->amount ?? 0) : ($this->amount ?? 0);
        
        return [
            'id' => $this->id,
            'name' => $this->name,
            'images' => is_array($this->image)
                ? array_map(fn($img) => url("storage/{$img}"), $this->image)
                : [],
            'title' => $this->title,
            'description' => $this->description,
            'price' => (int) $this->price,
            'is_available' => $amount > 0,
            'amount' => (int) $amount,
        ];
    }
}
