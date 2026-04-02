<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SearchResource extends JsonResource
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
            'uuid' => $this->uuid,
             'contract_type' => $this->contract_type_trans,
            'is_completed' => boolval($this->is_completed),
             'contract_ownership'=>$this->contract_ownership,
            'instrument_type' => $this->instrument_type,
            'instrument_number' => $this->instrument_number,
            'instrument_history' => $this->instrument_history,
            // 'total_price' => $this->total_price,
            'client_account_holder_name' => $this->client_account_holder_name,
            'id_num_of_property_owner_agent' => $this->id_num_of_property_owner_agent,
            'agent_iban_of_property_owner' => $this->agent_iban_of_property_owner,
            'agency_number_in_instrument_of_property_owner' => $this->agency_number_in_instrument_of_property_owner,
            'mobile_of_property_owner_agent' => $this->mobile_of_property_owner_agent,
             'created_at' => date('Y-m-d', strtotime($this->created_at)),
            'expiry_date' => isset($this->expiry_date) ? date('Y-m-d', strtotime($this->expiry_date)) : '',
         ];    }
}