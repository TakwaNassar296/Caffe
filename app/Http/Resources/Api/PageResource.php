<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
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
            'title' => $this->title ?? null,
            'description' => $this->content ?? null,
            'phone_number' => $this->phone_number ?? null,
            'image' => $this->image ? asset('storage/' . $this->image) : null,
            'slug' => $this->slug ?? null,
        ];
    }
}

