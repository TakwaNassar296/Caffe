<?php

namespace App\Http\Resources\Admin\V2\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\ReceivedContract;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $receivedContract = ReceivedContract::with('employee')
            ->where('contract_id', $this->id)
            ->first();

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'contract_type' => $this->contract_type,
            'amount_payment' => $this->contract->payments->amount ?? 'لم يتم الدفع',
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'status' => $this->status,
            'employee_name' => $receivedContract?->employee?->name ?? 'لم يتم الاستلام',
            'user_id' => $this->user_id,
            'user_name' => $this->user->name ?? null,
            'user_mobile' => $this->user->mobile ?? null,
            'ownership' => $this->contract_ownership,
            'instrument_type' => $this->instrument_type,
            'is_completed' => (bool) $this->is_completed,
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
