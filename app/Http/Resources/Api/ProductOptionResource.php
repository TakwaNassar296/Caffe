<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductOptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
          return [
            'id' => $this->id,
            'product_id' => $this->product_id ??null,
            'name' => $this->name ??null,
            'values' => $this->values ? ProductValueResource::collection($this->values): null,
        ];
    }
}
