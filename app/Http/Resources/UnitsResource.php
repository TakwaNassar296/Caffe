<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitsResource extends JsonResource
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
            'tootal_rooms'=>$this->tootal_rooms  ,
            'The_number_of_toilets'=>$this->The_number_of_toilets ,
            'The_number_of_halls'=> $this->The_number_of_halls ,
            'unit_number'=> $this-> unit_number,
             'electricity_meter_number'=> $this-> electricity_meter_number ,
             'The_number_of_kitchens'=> $this->The_number_of_kitchens ,
             'window_ac'=>$this->window_ac,
             'split_ac'=>$this->split_ac,
             'real_estates_units_id'=> $this->real_estates_units_id, 
            'unit_area'=> $this-> unit_area,
             'water_meter_number'=>$this->water_meter_number,
             
            'floor_number'=>$this->floor_number,
            'unit_usage_id'=> $this->unit_usage_id ,
            'unit_type_id'=>$this->unit_type_id,
            'unit_usage_name'=> $this->unitUsage->name_ar,
            'unit_type_name'=>$this->unitType->name_ar,
            'contract_type' => optional($this->realEstate)->contract_type, // Fixed to use relationship

        ];
    }
}
 