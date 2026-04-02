<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Alkoumi\LaravelHijriDate\Hijri;

class RealEstateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
 
        return [
            'id'=>$this->id,
            'instrument_type' => $this->instrument_type,
            'instrument_number' => $this->instrument_number,
            'instrument_history' => $this->instrument_history,
            'old_handwritten_photo' => $this->old_handwritten_photo,
            'photo_of_the_electronic' => $this->photo_of_the_electronic,
            'strong_argument_photo' => $this->strong_argument_photo,
            'real_estate_registry_number' => $this->real_estate_registry_number,
            'date_first_registration' => $this->date_first_registration,
            'name_owner' => $this->name_owner,
            'national_num' => $this->national_num,
            'DOB' => $this->DOB,
            'dob_hijri' => $this->dob_hijri,
            'mobile' => $this->mobile,
            'iban_bank' => $this->iban_bank,
            'Count_Units' => $this->units()->count(), 
            'name_real_estate' => $this->name_real_estate,
            'unit_number_of_real' => $this->unit_number_of_real,
            'property_type_name' => optional($this->propertyType)->name_ar,
            'property_usages_name' => optional($this->propertyUsages)->name_ar,
            'property_type_id' => $this->property_type_id,
            'type_real_estate_other'=>$this->type_real_estate_other,
            'property_usages_id' => $this->property_usages_id,
            'property_place_name' => optional($this->tenantEntityRegion)->name_ar,
            'property_city_name' => optional($this->tenantEntityCity)->name_ar,
            'property_place_id' => $this->property_place_id,
            'property_city_id' => $this->property_city_id,
            'contract_type' => $this->contract_type,
            'street' => $this->street,
            'postal_code' => $this->postal_code,
            'extra_figure' => $this->extra_figure,
            'neighborhood' => $this->neighborhood,
            'number_of_floors' => $this->number_of_floors,
            'building_number' => $this->building_number,
        ];
    }
}
