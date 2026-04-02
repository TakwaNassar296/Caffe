<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankAccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'bank_name' => $this->bank_name_trans,
            'bank_account_name' => $this->bank_account_name_trans,
            'bank_account_number' => $this->bank_account_number,
            'iban_number' => $this->iban_number,
        ];
    }
}
