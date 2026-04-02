<?php

namespace App\Http\Resources\Api\Driver;



use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id  ?? null,
            'user_name' => $this->user->first_name ?? null,
            'user_image' => $this->user->image ? asset('storage/' . $this->user->image) : asset('images/default.png'),
            'rating' => $this->rating ?? null,
            'comment' => $this->comment ?? null,
            'created_at' => $this->created_at ? $this->created_at->format('F d, Y'): null,
        ];
    }
}
