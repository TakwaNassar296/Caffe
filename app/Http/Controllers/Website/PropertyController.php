<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\RealEstate;
use App\Models\UnitsReal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PropertyController extends Controller
{
    public function property(Request $request, $uuid)
    {
       
        $contract = Contract::where('uuid', $uuid)->firstOrFail();
        $user_id = Auth::user()->id;
    
      
        $request->validate([
            'name_real_estate' => 'required|string|max:255',
         ]);
    
        $realData = [
            'user_id' => $user_id ?? null,
            'iban_bank' => $contract->property_owner_iban ?? null,
            'contract_type' => $contract->contract_type ?? null,
            'date_first_registration' => $contract->date_first_registration ?? null,
            'real_estate_registry_number' => $contract->real_estate_registry_number ?? null,
            'name_real_estate' => $request->input('name_real_estate') ?? null,
            'name_owner' => $request->input('name_owner') ?? null,
            'unit_number_of_real' => $contract->unit_number_of_real ?? null,
            'unit_number' => $contract->unit_number ?? null,
            'instrument_number' => $contract->instrument_number ?? null,
            'instrument_history' => $contract->instrument_history ?? null,
            'instrument_type' => $contract->instrument_type ?? null,
            'property_city_id' => $contract->property_city_id ?? null,
            'street' => $contract->street ?? null,
            'number_of_floors' => $contract->number_of_floors ?? null,
            'postal_code' => $contract->postal_code ?? null,
            'extra_figure' => $contract->extra_figure ?? null,
            'type_real_estate_other' => $contract->type_real_estate_other ?? null,
            'national_num' => $contract->property_owner_id_num ?? null,
            'dob_hijri' => $contract->property_owner_dob_hijri ?? null,
            'mobile' => $contract->property_owner_mobile ?? null,
            'neighborhood' => $contract->neighborhood ?? null,
            'property_place_id' => $contract->property_place_id ?? null,
            'building_number' => $contract->building_number ?? null,
            'property_type_id' => $contract->property_type_id ?? null,
            'property_usages_id' => $contract->property_usages_id ?? null,
         
        ];
    
        $real = RealEstate::create($realData);
        $real_id = $real->id;
    
        UnitsReal::create([
            'real_estates_units_id' => $real_id,
            'user_id' => $user_id,
            'unit_number' => $contract->unit_number ?? null,
            'unit_area' => $contract->unit_area ?? null,
            'electricity_meter_number' => $contract->electricity_meter_number ?? null,
            'water_meter_number' => $contract->water_meter_number ?? null,
            'Gasmeter' => $contract->Gasmeter ?? null,
            'window_ac' => $contract->window_ac ?? null,
            'split_ac' => $contract->split_ac ?? null,
            'tootal_rooms' => $contract->tootal_rooms ?? null,
            'The_number_of_the_toilet' => $contract->The_number_of_the_toilet ?? null,
            'The_number_of_kitchens' => $contract->The_number_of_kitchens ?? null,
            'unit_usage_id' => $contract->unit_usage_id ?? null,
            'unit_type_id' => $contract->unit_type_id ?? null,
            'floor_number' => $contract->floor_number ?? null,
            'The_number_of_halls' => $contract->The_number_of_halls ?? null,
        ]);
    
        return redirect()->back()->with('success', 'تم اضافة عقار جديد');
    }
    
    protected function getValidInteger($value)
    {
        return is_numeric($value) ? (int) $value : null;
    }
    
    protected function getValidDate($value)
    {
        return $value && \DateTime::createFromFormat('Y-m-d', $value) ? $value : null;
    }
}    