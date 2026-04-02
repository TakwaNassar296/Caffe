<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
         return [
            'id'          => $this->id ??null,
            'name'        => $this->name ??null,
            'card_number' => $this->card_number ??null,
            'cvv'         => $this->cvv ??null,
            'expire_date' => $this->expire_date ??null,
        ];
    }
}
