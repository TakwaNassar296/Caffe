<?php

namespace App\Http\Resources\Branch;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryConfirmationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'request_id' => $this->id,
            'delivery_status' => $this->delivery_status,
            'delivery_feedback' => $this->delivery_feedback,
            'delivery_confirmed_at' => $this->delivery_confirmed_at?->toDateTimeString(),
        ];
    }
}
