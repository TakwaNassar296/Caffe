<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{

     private function processImages($images)
    {
        if (is_string($images)) {
            $images = json_decode($images, true) ?: [];
        }
        return array_map(fn($image) => url("/storage/{$image}"), $images);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'description'   => $this->description,
            'code'          => $this->code,
            'image'         => asset('storage/' . $this->image),
            'images'        => $this->processImages($this->images),
            'opening_date'  => $this->opening_date,
            'close_date'    => $this->close_date,
            // 'scope_work'    => $this->scope_work,
            'latitude'      => $this->latitude,
            'longitude'     => $this->longitude,
            'phone_number'  => $this->phone_number,
            'scope_work'    => $this->scope_work,
            'delivery_time' => '10 - 15 minutes',
            'is_delivery' =>  $this->is_delivery ?? 1,
            // 'total_rating'  => $this->reviews()->sum('rating'),
            // 'average_rating'=> round($this->reviews()->avg('rating'), 1), 
            // 'total_orders'  => $this->orders()->count(),.

            'total_rating'   => $this->reviews()->exists()
                ? $this->reviews()->sum('rating')
                : 0,

            'average_rating' => $this->reviews()->exists()
                ? round($this->reviews()->avg('rating'), 1)
                : 0,

            'total_orders'   => $this->orders()->exists()
                ? $this->orders()->count()
                : 0,

            'followed' => auth('user')->check()
                ? $this->users()->where('user_id', auth('user')->id())->exists()
                : false,
        ];
    }
}
