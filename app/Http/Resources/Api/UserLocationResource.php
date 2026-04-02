<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserLocationResource extends JsonResource
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
            'name_address' => $this->name_address,
            'building_number' => $this->building_number,
            'floor' => $this->floor,
            'apartment' => $this->apartment,
            'address_description' => $this->address_description,
            'address_title' => $this->address_title,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone_number' => $this->phone_number,
        ];
    }
}
