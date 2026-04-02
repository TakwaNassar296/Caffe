<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RankResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name ??null,
            'title'       => $this->title ?? null,
            'image'       => $this->image ? asset('storage/' . $this->image) : null,
            'min_stars'  => $this->min_stars??null,
            'max_stars'  => $this->max_stars??null,
            "points_increment" => $this->points_increment??null,
            "stars_increment" => $this->stars_increment??null,
            'description' => $this->description??null,
            "badge_color" => $this->badge_color??null,
       
        ];
    }
}
