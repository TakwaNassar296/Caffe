<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SiteSettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    private function processImages($images)
    {
        if (empty($images)) {
            return [];
        }
        
        if (is_string($images)) {
            $images = json_decode($images, true) ?: [];
        }
        
        if (!is_array($images)) {
            return [];
        }
        
        return array_map(fn($image) => url("/storage/{$image}"), $images);
    }

    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'image' => $this->image ? asset('storage/' . $this->image) : null,
            'app_link_google_play' => $this->app_link_google_play,
            'app_link_app_store' => $this->app_link_app_store,
            'images_slider' => $this->processImages($this->images),
        ];
    }
}