<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ContractResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Getting the authenticated user
        $authenticatedUser = Auth::user();

        // Total contract price calculation
        $totalContractPrice = $this->getPriceContractAttribute();

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'contract_type' => $this->contract_type,
            'property_owner_id_num' => $this->property_owner_id_num,
            'is_completed' => boolval($this->is_completed),
            'tenant_id_num' => $this->tenant_id_num,
            'contract_ownership' => $this->contract_ownership,
            'id_num_of_property_owner_agent' => $this->id_num_of_property_owner_agent, 
            'dob_hijri_of_property_owner_agent' => $this->dob_hijri_of_property_owner_agent,
            'mobile_of_property_owner_agent' => $this->mobile_of_property_owner_agent,
            'user' => $authenticatedUser ? $authenticatedUser->fname . ' ' . $authenticatedUser->lname : null,
            'created_at' => $this->created_at->format('Y-m-d'),
            // 'total_price' => $totalContractPrice,
            // 'step' => $this->step,
        ];
    }
}
