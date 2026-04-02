<?php

namespace App\Models;

use App\Models\City;
use App\Models\Contract;
use App\Models\ReaEstatType;
use App\Models\ReaEstatUsage;
use App\Models\Region;
use App\Models\UnitType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RealEstate extends Model
{
    protected $casts = [
        'property_type_id' => 'integer',
        'property_usages_id' => 'integer',
    ];
    use HasFactory;
    protected $fillable = [
        'dob_hijri',
        'name_owner','property_owner_id_num', 'property_owner_dob_hijri', 'property_owner_mobile', 'property_owner_iban', 'name_real_estate', 
        'unit_number_of_real', 'property_type_id', 'property_usages_id', 'property_place_id', 'neighborhood','user_id',
        'contract_type','instrument_type','id_num_of_property_owner_agent','id_num_of_property_owner_agent ',
        'property_city_id', 'street', 'number_of_floors','building_number',
        'postal_code','extra_figure','real_estate_registry_number'
        ,'date_first_registration','contract_ownership','add_legal_agent_of_owner','dob_of_property_owner_agent',
        'property_owner_is_deceased','instrument_number','instrument_history', 'type_real_estate_other',
        'mobile_of_property_owner_agent','agency_number_in_instrument_of_property_owner','agency_instrument_date_of_property_owner'
    ];
  
    
 
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function units()
    {
      return $this->hasMany(UnitsReal::class,'real_estates_units_id');        
    }


    public function propertyType()
    {
        return $this->belongsTo(ReaEstatType::class, 'property_type_id');
    }

    public function propertyUsages()
    {
        return $this->belongsTo(ReaEstatUsage::class, 'property_usages_id');
    }


    public function tenantEntityCity()
    {
        return $this->belongsTo(City::class, 'property_city_id');
    }

    public function tenantEntityRegion()
    {
        return $this->belongsTo(Region::class, 'property_place_id');
    }

   
    public function contracts()
    {
        return $this->hasMany(Contract::class, 'real_id');
    }
    
 

 
    
 

  

   


}
