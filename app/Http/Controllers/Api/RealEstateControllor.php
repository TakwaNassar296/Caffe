<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RealEstateRequest;
 
 use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Resources\RealEstateResource;
 
use App\Http\Traits\Responser;
use App\Models\City;
use App\Models\ReaEstatType;
use App\Models\RealEstate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;


class RealEstateControllor extends Controller
{
    use Responser;
 
    public function index()
    {
        $user = auth()->user();
        $data = RealEstate::where('user_id', $user->id)->get();
    
        $response = $data->map(function ($realEstate) {  
            return [
                'id' => $realEstate->id,
                'name' => $realEstate->name_real_estate,
                'name_owner' => $realEstate->name_owner,
                'contract_type' => $realEstate->contract_type,
                'unit_number_of_real'=>$realEstate->unit_number_of_real,
                'property_place_name' => $realEstate->tenantEntityRegion->name_trans ?? null, 
                'property_city_name' => $realEstate->tenantEntityCity->name_trans ?? null,  
            ];
        });
    
        return $this->apiResponse($data, trans('api.real_estate'));
    }
    


    public function all()
    {
        $user = auth()->user();
    
        // Fetching real estates related to the authenticated user
        $realEstates = RealEstate::where('user_id', $user->id)->get();
    
        if(!$realEstates)
        {
             return $this->errorMessage(trans('api.not_have_real'), 404);

        }
        $response = $realEstates->map(function ($realEstate) {
            return [
                'id' => $realEstate->id,
                'name_real_estate' => $realEstate->name_real_estate,
                'contract_type' => $realEstate->contract_type,
                'name_owner' => $realEstate->name_owner,
                'property_city_name' => $realEstate->tenantEntityCity->name_trans ?? null,  
                'property_place_name' => $realEstate->tenantEntityRegion->name_trans ?? null,  
                'unit_number_of_real'=>$realEstate->unit_number_of_real,
                'Number_of_units_already_existence' => (string) $realEstate->units()->count(),
              ];
        });
        return $this->apiResponse($response, trans('api.real_estate'));
    }
    

     //return realestat
     public function show($id)
     {
         try {
             $user = auth()->user();
             $realEstate = RealEstate::where('user_id', $user->id)->findOrFail($id);
     
             // Create an array with the specific properties of the $realEstate object
             $realEstateData = [
                 'id'=>$realEstate->id,
                 'add_legal_agent_of_owner' => $realEstate->add_legal_agent_of_owner,
                 'id_num_of_property_owner_agent' => $realEstate->id_num_of_property_owner_agent,
                 'dob_of_property_owner_agent' => $realEstate->dob_of_property_owner_agent,
                 'mobile_of_property_owner_agent' => $realEstate->mobile_of_property_owner_agent,
                 'agency_number_in_instrument_of_property_owner' => $realEstate->agency_number_in_instrument_of_property_owner,
                 'agency_instrument_date_of_property_owner' => $realEstate->agency_instrument_date_of_property_owner,
                 'property_owner_is_deceased' => $realEstate->property_owner_is_deceased,
                 'contract_ownership' => $realEstate->contract_ownership,
                 'instrument_type' => $realEstate->instrument_type,
                 'contract_type' => $realEstate->contract_type,
                 'date_first_registration' => $realEstate->date_first_registration,
                 'real_estate_registry_number' => $realEstate->real_estate_registry_number,
                 'property_owner_dob_hijri' => $realEstate->property_owner_dob_hijri,
                 'instrument_number' => $realEstate->instrument_number,
                 'instrument_history' => $realEstate->instrument_history,
                 'name_owner' => $realEstate->name_owner,
                 'property_owner_id_num' => $realEstate->property_owner_id_num,
                 'unit_number_of_real' => $realEstate->unit_number_of_real,
                 'property_owner_mobile'=>$realEstate->property_owner_mobile,
                 'property_owner_iban'=>$realEstate->property_owner_iban,
                 'name_real_estate' => $realEstate->name_real_estate,
                 'number_of_floors' => $realEstate->number_of_floors,
                 'property_type_id' => $realEstate->property_type_id,
                 'property_usages_id' => $realEstate->property_usages_id,
                 'property_type_name' => $realEstate->propertyType->name_ar,
                 'property_usages_name' => $realEstate->propertyUsages->name_ar,
                 'type_real_estate_other' => $realEstate->type_real_estate_other,
                 'property_city_id' => $realEstate->property_city_id,
                 'property_city_name' => $realEstate->tenantEntityCity->name_ar,  
                 'property_place_id' => $realEstate->property_place_id,
                 'property_place_name' => $realEstate->tenantEntityRegion->name_ar,  
                 'street' => $realEstate->street,
                 'neighborhood' => $realEstate->neighborhood,
                 'building_number' => $realEstate->building_number,
                 'postal_code' => $realEstate->postal_code,
                 'extra_figure' => $realEstate->extra_figure,
                 'user_id' => $realEstate->user_id,
                 'step' => $realEstate->step,
                 'is_deleted' => $realEstate->is_deleted,
             ];
     
             return $this->apiResponse($realEstateData, trans('api.real_estate'), 200);
         } catch (ModelNotFoundException $e) {
             return $this->errorMessage(trans('api.not_have_real'), 404);
         } catch (\Exception $e) {
             return $this->errorMessage(trans('api.error_occurred'), 500);
         }
     }
     
    public function showUnits($id)
    {
        try {
            $user = auth()->user();
            $realEstate = RealEstate::with('units')->where('user_id', $user->id)->findOrFail($id);
            
            return $this->apiResponse($realEstate,trans('api.have_real'),200);
        } catch (ModelNotFoundException $e) {
            return $this->errorMessage(trans('api.not_have_real'));
        }
    }
    public function step1(Request $request)
{
    $user=Auth::user();
    $rules = [
        'name_real_estate'=>'nullable',
        'contract_ownership' => 'required|in:owner,tenant',
        'instrument_number' => [Rule::requiredIf($request->instrument_type == 'electronic')],
        'instrument_history' => [Rule::requiredIf($request->instrument_type == 'electronic')],
        'real_estate_registry_number' => [Rule::requiredIf($request->instrument_type == 'strong_argument')],
        'date_first_registration' => [Rule::requiredIf($request->instrument_type == 'strong_argument')],
        'property_type_id' => 'required|exists:rea_estat_types,id',
        'property_owner_is_deceased' => 'required|boolean',
        'number_of_floors' => 'required',
        'contract_type' => 'required|in:housing,commercial',
        'instrument_type' => 'nullable|in:electronic,strong_argument|required_if:property_owner_is_deceased,1',
        'property_usages_id' => 'required_if:instrument_type,electronic,strong_argument',
        'unit_number_of_real' => 'required_if:instrument_type,electronic,strong_argument|integer',
    ];

    $this->validate($request, $rules);

     $realEstate = RealEstate::create([
        'user_id'=>$user->id,
        'contract_ownership' => $request->contract_ownership,
        'contract_type'=>$request->contract_type ,
        'instrument_number' => $request->instrument_number,
        'instrument_history' => $request->instrument_history,
        'real_estate_registry_number' => $request->real_estate_registry_number,
        'date_first_registration' => $request->date_first_registration,
        'property_owner_is_deceased' => $request->property_owner_is_deceased,
        'unit_number_of_real' => $request->unit_number_of_real,
        'instrument_type' => $request->instrument_type,
        'property_type_id' => $request->property_type_id,
        'property_usages_id' => $request->property_usages_id,
        'number_of_floors' => $request->number_of_floors,
        'step' => 2  
    ]);

    return response()->json([
        'message' => trans('api.created_success'),
        'code' => 200,
        'success' => true,
        'data' => $realEstate
    ]);
}

public function step2(Request $request)
{
    $user=Auth::user();

    $rules = [
        'id' => 'required|exists:real_estates,id',
        'property_place_id' => 'required|integer|exists:regions,id',
        'property_city_id' => 'required|integer|exists:cities,id',
        'neighborhood' => 'required|string|max:255',
        'street' => 'required|string|max:255',
        'building_number' => 'required|string|max:50',
        'postal_code' => 'required|string|max:20',
        'extra_figure' => 'nullable|string|max:255',
    ];

    $this->validate($request, $rules);

    // Get existing RealEstate record
    $realEstate = RealEstate::find($request->id);

    if (!$realEstate) {
        return response()->json([
            'message' => trans('api.real_estate_not_found'),
            'code' => 404,
            'success' => false
        ]);
    }

    // Check if city exists in the selected region
    $city = City::where('id', $request->property_city_id)
                ->where('region_id', $request->property_place_id)
                ->first();

    if (!$city) {
        return $this->errorMessage(trans('api.city_not_include_region'));
    }

    // Update the real estate with new data
    $realEstate->update([
        'property_place_id' => $request->property_place_id,
        'property_city_id' => $request->property_city_id,
        'neighborhood' => $request->neighborhood,
        'street' => $request->street,
        'building_number' => $request->building_number,
        'postal_code' => $request->postal_code,
        'extra_figure' => $request->extra_figure,
        'user_id'=>$user->id,
        'step' => 3 // Update step to 3
    ]);

    return response()->json([
        'message' => trans('api.updated_success'),
        'code' => 200,
        'success' => true,
        'data' => $realEstate
    ]);
}

public function step3(Request $request)
{
    $user=Auth::user();

    $rules = [
        'id' => 'required|exists:real_estates,id',
         'name_owner' => 'required|string',
        'property_owner_id_num' => 'required|min:10',
        'property_owner_dob_hijri' => 'required',
        'property_owner_mobile' => 'required|min:10|regex:/^05[0-9]{8}$/',
        'property_owner_iban' => 'nullable|min:22',
        'add_legal_agent_of_owner'=>'required',
        'id_num_of_property_owner_agent' => 'nullable|required_if:add_legal_agent_of_owner,1|min:10',
        'dob_of_property_owner_agent' => 'nullable|required_if:add_legal_agent_of_owner,1',
        'mobile_of_property_owner_agent' => 'nullable|required_if:add_legal_agent_of_owner,1|min:10|regex:/^05[0-9]{8}$/',
        'agency_number_in_instrument_of_property_owner' => 'nullable|required_if:add_legal_agent_of_owner,1',
        'agency_instrument_date_of_property_owner' => 'nullable|required_if:add_legal_agent_of_owner,1',
    ];

    $this->validate($request, $rules);

    // Get existing RealEstate record
    $realEstate = RealEstate::find($request->id);

    if (!$realEstate) {
        return response()->json([
            'message' => trans('api.real_estate_not_found'),
            'code' => 404,
            'success' => false
        ]);
    }

    // Update the real estate with new data
    $realEstate->update([
        'name_owner' => $request->name_owner,
        'user_id'=>$user->id,
        'add_legal_agent_of_owner'=>$request->add_legal_agent_of_owner,
        'name_real_estate'=> $request->name_real_estate,
        'property_owner_id_num' => $request->property_owner_id_num,
        'property_owner_dob_hijri' => $request->property_owner_dob_hijri,
        'property_owner_mobile' => $request->property_owner_mobile,
        'property_owner_iban' => $request->property_owner_iban,
        'id_num_of_property_owner_agent' => $request->id_num_of_property_owner_agent,
        'dob_of_property_owner_agent' => $request->dob_of_property_owner_agent,
        'mobile_of_property_owner_agent' => $request->mobile_of_property_owner_agent,
        'agency_number_in_instrument_of_property_owner' => $request->agency_number_in_instrument_of_property_owner,
        'agency_instrument_date_of_property_owner' => $request->agency_instrument_date_of_property_owner,
        'step' => 4  
    ]);

    $realEstateData = [
        'id'=>$realEstate->id,

        'add_legal_agent_of_owner' => $realEstate->add_legal_agent_of_owner,
        'id_num_of_property_owner_agent' => $realEstate->id_num_of_property_owner_agent,
        'dob_of_property_owner_agent' => $realEstate->dob_of_property_owner_agent,
        'mobile_of_property_owner_agent' => $realEstate->mobile_of_property_owner_agent,
        'agency_number_in_instrument_of_property_owner' => $realEstate->agency_number_in_instrument_of_property_owner,
        'agency_instrument_date_of_property_owner' => $realEstate->agency_instrument_date_of_property_owner,
        'property_owner_is_deceased' => $realEstate->property_owner_is_deceased,
        'contract_ownership' => $realEstate->contract_ownership,
         'instrument_type' => $realEstate->instrument_type,
    
        'date_first_registration' => $realEstate->date_first_registration,
        'real_estate_registry_number' => $realEstate->real_estate_registry_number,
        'property_owner_dob_hijri' => $realEstate->property_owner_dob_hijri,
        'instrument_number' => $realEstate->instrument_number,
        'instrument_history' => $realEstate->instrument_history,
        'name_owner' => $realEstate->name_owner,
        'property_owner_id_num' => $realEstate->property_owner_id_num,
        'unit_number_of_real' => $realEstate->unit_number_of_real,
        'property_owner_mobile' => $request->property_owner_mobile,
        'property_owner_iban' => $request->property_owner_iban,
        'name_real_estate' => $realEstate->name_real_estate,
        'number_of_floors' => $realEstate->number_of_floors,
        'property_type_id' => $realEstate->property_type_id,
        'property_usages_id' => $realEstate->property_usages_id,
        'property_type_name' => $realEstate->propertyType->name_ar,
        'property_usages_name' => $realEstate->propertyUsages->name_ar,
        'type_real_estate_other' => $realEstate->type_real_estate_other,
        'property_city_id' => $realEstate->property_city_id,
        'property_city_name' => $realEstate->tenantEntityCity->name_ar,  
        'property_place_id' => $realEstate->property_place_id,
        'property_place_name' => $realEstate->tenantEntityRegion->name_ar,  
        'street' => $realEstate->street,
        'neighborhood' => $realEstate->neighborhood,
        'building_number' => $realEstate->building_number,
        'postal_code' => $realEstate->postal_code,
        'extra_figure' => $realEstate->extra_figure,
        'user_id' => $realEstate->user_id,
        'step' => $realEstate->step,
        'is_deleted' => $realEstate->is_deleted,
    ];

    return response()->json([
        'message' => trans('api.updated_success'),
        'code' => 200,
        'success' => true,
        'data' => $realEstateData
    ]);
}

    public function updateStep1(Request $request){

    $rules = [
        'id' => 'required|exists:real_estates,id',  
        'contract_ownership' => 'required|in:owner,tenant',
        'instrument_number' => [Rule::requiredIf($request->instrument_type == 'electronic')],
        'instrument_history' => [Rule::requiredIf($request->instrument_type == 'electronic')],
        'real_estate_registry_number' => [Rule::requiredIf($request->instrument_type == 'strong_argument')],
        'date_first_registration' => [Rule::requiredIf($request->instrument_type == 'strong_argument')],
        'property_type_id' => 'required|exists:rea_estat_types,id',
        'property_owner_is_deceased' => 'required|boolean',
        'number_of_floors' => 'required',
        'instrument_type' => 'nullable|in:electronic,strong_argument|required_if:property_owner_is_deceased,1',
        'property_usages_id' => 'required_if:instrument_type,electronic,strong_argument',
        'unit_number_of_real' => 'required_if:instrument_type,electronic,strong_argument|integer',
    ];

    $this->validate($request, $rules);

    $realEstate = RealEstate::findOrFail($request->id); // Find or fail to ensure existing record

    if ($realEstate->is_completed) {
        return $this->errorMessage(trans('api.completed_contract'));
    }

    $data = $request->only([
        'contract_ownership', 'instrument_number', 'instrument_history', 
        'real_estate_registry_number', 'date_first_registration', 
        'property_owner_is_deceased', 'unit_number_of_real', 'instrument_type', 
        'property_type_id', 'property_usages_id', 'number_of_floors'
    ]);

    if ($request->instrument_type == 'electronic') {
        $data['instrument_number'] = $request->instrument_number;
        $data['instrument_history'] = date('Y-m-d', strtotime($request->instrument_history));
    } elseif ($request->instrument_type == 'strong_argument') {
        $data['real_estate_registry_number'] = $request->real_estate_registry_number;
        $data['date_first_registration'] = $request->date_first_registration;
    }

    $data['step'] = 2;
    $realEstate->update($data);

    $realEstateData = [
        'id'=>$realEstate->id,

        'add_legal_agent_of_owner' => $realEstate->add_legal_agent_of_owner,
        'id_num_of_property_owner_agent' => $realEstate->id_num_of_property_owner_agent,
        'dob_of_property_owner_agent' => $realEstate->dob_of_property_owner_agent,
        'mobile_of_property_owner_agent' => $realEstate->mobile_of_property_owner_agent,
        'agency_number_in_instrument_of_property_owner' => $realEstate->agency_number_in_instrument_of_property_owner,
        'agency_instrument_date_of_property_owner' => $realEstate->agency_instrument_date_of_property_owner,
        'property_owner_is_deceased' => $realEstate->property_owner_is_deceased,
        'contract_ownership' => $realEstate->contract_ownership,
         'instrument_type' => $realEstate->instrument_type,
        'contract_type' => $realEstate->contract_type,
        'date_first_registration' => $realEstate->date_first_registration,
        'real_estate_registry_number' => $realEstate->real_estate_registry_number,
        'property_owner_dob_hijri' => $realEstate->property_owner_dob_hijri,
        'instrument_number' => $realEstate->instrument_number,
        'instrument_history' => $realEstate->instrument_history,
        'name_owner' => $realEstate->name_owner,
        'property_owner_id_num' => $realEstate->property_owner_id_num,
        'unit_number_of_real' => $realEstate->unit_number_of_real,
        'property_owner_mobile'=>$realEstate->property_owner_mobile,
        'property_owner_iban'=>$realEstate->property_owner_iban,
        
        'name_real_estate' => $realEstate->name_real_estate,
        'number_of_floors' => $realEstate->number_of_floors,
        'property_type_id' => $realEstate->property_type_id,
        'property_usages_id' => $realEstate->property_usages_id,
        'property_type_name' => $realEstate->propertyType->name_ar,
        'property_usages_name' => $realEstate->propertyUsages->name_ar,
        'type_real_estate_other' => $realEstate->type_real_estate_other,
        'property_city_id' => $realEstate->property_city_id,
        'property_city_name' => $realEstate->tenantEntityCity->name_ar,  
        'property_place_id' => $realEstate->property_place_id,
        'property_place_name' => $realEstate->tenantEntityRegion->name_ar,  
        'street' => $realEstate->street,
        'neighborhood' => $realEstate->neighborhood,
        'building_number' => $realEstate->building_number,
        'postal_code' => $realEstate->postal_code,
        'extra_figure' => $realEstate->extra_figure,
        'user_id' => $realEstate->user_id,
        'step' => $realEstate->step,
        'is_deleted' => $realEstate->is_deleted,
    ];


    return response()->json([
        'message' => trans('api.updated_success'),
        'code' => 200,
        'success' => true,
        'data' => $realEstateData
    ]);
}

public function updateStep2(Request $request){
    $rules = [
        'id' => 'required|exists:real_estates,id', 
        'property_place_id' => 'required|integer|exists:regions,id',
        'property_city_id' => 'required|integer|exists:cities,id',
        'neighborhood' => 'required|string|max:255',
        'street' => 'required|string|max:255',
        'building_number' => 'required|string|max:50',
        'postal_code' => 'required|string|max:20',
        'extra_figure' => 'required|string|max:255',
    ];

    $this->validate($request, $rules);

    $realEstate = RealEstate::findOrFail($request->id); 

    $city = City::where('id', $request->property_city_id)
                ->where('region_id', $request->property_place_id)
                ->first();

    if (!$city) {
        return $this->errorMessage(trans('api.city_not_include_region'));
    }

    $data = $request->only([
        'property_place_id', 'property_city_id', 'neighborhood', 
        'street', 'building_number', 'postal_code', 'extra_figure'
    ]);

    $data['step'] = 3;
    $realEstate->update($data);

    $realEstateData = [
        'id'=>$realEstate->id,

        'add_legal_agent_of_owner' => $realEstate->add_legal_agent_of_owner,
        'id_num_of_property_owner_agent' => $realEstate->id_num_of_property_owner_agent,
        'dob_of_property_owner_agent' => $realEstate->dob_of_property_owner_agent,
        'mobile_of_property_owner_agent' => $realEstate->mobile_of_property_owner_agent,
        'agency_number_in_instrument_of_property_owner' => $realEstate->agency_number_in_instrument_of_property_owner,
        'agency_instrument_date_of_property_owner' => $realEstate->agency_instrument_date_of_property_owner,
        'property_owner_is_deceased' => $realEstate->property_owner_is_deceased,
        'contract_ownership' => $realEstate->contract_ownership,
         'instrument_type' => $realEstate->instrument_type,
        'contract_type' => $realEstate->contract_type,
        'date_first_registration' => $realEstate->date_first_registration,
        'real_estate_registry_number' => $realEstate->real_estate_registry_number,
        'property_owner_dob_hijri' => $realEstate->property_owner_dob_hijri,
        'instrument_number' => $realEstate->instrument_number,
        'instrument_history' => $realEstate->instrument_history,
        'name_owner' => $realEstate->name_owner,
        'property_owner_id_num' => $realEstate->property_owner_id_num,
        'unit_number_of_real' => $realEstate->unit_number_of_real,
        'property_owner_mobile' => $request->property_owner_mobile,
        'property_owner_iban' => $request->property_owner_iban,
        'name_real_estate' => $realEstate->name_real_estate,
        'number_of_floors' => $realEstate->number_of_floors,
        'property_type_id' => $realEstate->property_type_id,
        'property_usages_id' => $realEstate->property_usages_id,
        'property_type_name' => $realEstate->propertyType->name_ar,
        'property_usages_name' => $realEstate->propertyUsages->name_ar,
        'type_real_estate_other' => $realEstate->type_real_estate_other,
        'property_city_id' => $realEstate->property_city_id,
        'property_city_name' => $realEstate->tenantEntityCity->name_ar,  
        'property_place_id' => $realEstate->property_place_id,
        'property_place_name' => $realEstate->tenantEntityRegion->name_ar,  
        'street' => $realEstate->street,
        'neighborhood' => $realEstate->neighborhood,
        'building_number' => $realEstate->building_number,
        'postal_code' => $realEstate->postal_code,
        'extra_figure' => $realEstate->extra_figure,
        'user_id' => $realEstate->user_id,
        'step' => $realEstate->step,
        'is_deleted' => $realEstate->is_deleted,
    ];


    return response()->json([
        'message' => trans('api.updated_success'),
        'code' => 200,
        'success' => true,
        'data' => $realEstateData
    ]);
}

public function updateStep3(Request $request)
{
    $rules = [
        'id' => 'required|exists:real_estates,id',  
        'name_owner' => 'nullable|string',
        'name_real_estate' => 'sometimes|string',
        'property_owner_id_num' => 'nullable|min:10',
        'property_owner_dob_hijri' => 'nullable', // Ensure the date format
        'property_owner_mobile' => 'nullable|min:9|regex:/^05[0-9]{8}$/',
        'property_owner_iban' => 'nullable|min:22',
        'add_legal_agent_of_owner'=>'nullable',
        'id_num_of_property_owner_agent' => 'nullable|required_if:add_legal_agent_of_owner,1|min:10',
        'dob_of_property_owner_agent' => 'nullable|required_if:add_legal_agent_of_owner,1',
        'mobile_of_property_owner_agent' => 'nullable|required_if:add_legal_agent_of_owner,1|min:10|regex:/^05[0-9]{8}$/',
        'agency_number_in_instrument_of_property_owner' => 'nullable|required_if:add_legal_agent_of_owner,1',
        'agency_instrument_date_of_property_owner' => 'nullable|required_if:add_legal_agent_of_owner,1',
    ];

    $messages = [
        'name_owner.required' => 'اسم المالك مطلوب.',
        'property_owner_id_num.required' => 'رقم هوية المالك مطلوب.',
        'property_owner_id_num.min' => 'رقم هوية المالك لا يقل عن عشرة أرقام.',
        'property_owner_dob_hijri.required' => 'تاريخ ميلاد المالك مطلوب.',
        'property_owner_dob_hijri.date_format' => 'تاريخ ميلاد المالك يجب أن يكون بالشكل: يوم-شهر-سنة.',
        'property_owner_mobile.required' => 'رقم جوال المالك مطلوب.',
        'property_owner_mobile.min' => 'رقم جوال المالك 10 ارقام علي الاقل.',
        'property_owner_mobile.regex' => 'رقم جوال المالك  يبدا ب 05 ويتبعه ثمانية ارقام',
        'property_owner_iban.required' => 'رقم الآيبان الخاص بالمالك مطلوب.',
        'property_owner_iban.min' => 'رقم الآيبان المالك يجب ان يكون 22 رقم',
        'id_num_of_property_owner_agent.required_if' => 'رقم هوية وكيل المالك مطلوب عند وجود وكيل.',
        'id_num_of_property_owner_agent.min' => 'رقم هوية وكيل المالك 10 أرقام علي الاقل.',
        'dob_of_property_owner_agent.required_if' => 'تاريخ ميلاد وكيل المالك مطلوب عند وجود وكيل.',
        'dob_of_property_owner_agent.date_format' => 'تاريخ ميلاد وكيل المالك يجب أن يكون بالشكل: يوم-شهر-سنة.',
        'mobile_of_property_owner_agent.required_if' => 'رقم جوال وكيل المالك مطلوب عند وجود وكيل.',
        'mobile_of_property_owner_agent.min' => 'رقم جوال وكيل المالك  يبدا ب 05 ويتبعه ثمانية ارقام',
        'mobile_of_property_owner_agent.regex' => 'رقم جوال وكيل المالك  يبدا ب 05 ويتبعه ثمانية ارقام',
        'agency_number_in_instrument_of_property_owner.required_if' => 'رقم الوكالة مطلوب عند وجود وكيل.',
        'agency_instrument_date_of_property_owner.required_if' => 'تاريخ الوكالة مطلوب عند وجود وكيل.',
    ];

    $this->validate($request, $rules, $messages);

    $realEstate = RealEstate::findOrFail($request->id);  

    $data = $request->only([
        'name_real_estate','add_legal_agent_of_owner',
        'name_owner', 'property_owner_id_num', 'property_owner_dob_hijri', 
        'property_owner_mobile', 'property_owner_iban', 
        'id_num_of_property_owner_agent', 'dob_of_property_owner_agent', 
        'mobile_of_property_owner_agent', 'agency_number_in_instrument_of_property_owner', 
        'agency_instrument_date_of_property_owner'
    ]);

    $data['step'] = 4;
    $realEstate->update($data);

    $realEstateData = [
        'id'=>$realEstate->id,
        'add_legal_agent_of_owner' => $realEstate->add_legal_agent_of_owner,
        'id_num_of_property_owner_agent' => $realEstate->id_num_of_property_owner_agent,
        'dob_of_property_owner_agent' => $realEstate->dob_of_property_owner_agent,
        'mobile_of_property_owner_agent' => $realEstate->mobile_of_property_owner_agent,
        'agency_number_in_instrument_of_property_owner' => $realEstate->agency_number_in_instrument_of_property_owner,
        'agency_instrument_date_of_property_owner' => $realEstate->agency_instrument_date_of_property_owner,
        'property_owner_is_deceased' => $realEstate->property_owner_is_deceased,
        'contract_ownership' => $realEstate->contract_ownership,
         'instrument_type' => $realEstate->instrument_type,
        'contract_type' => $realEstate->contract_type,
        'date_first_registration' => $realEstate->date_first_registration,
        'real_estate_registry_number' => $realEstate->real_estate_registry_number,
        'property_owner_dob_hijri' => $realEstate->property_owner_dob_hijri,
        'instrument_number' => $realEstate->instrument_number,
        'instrument_history' => $realEstate->instrument_history,
        'name_owner' => $realEstate->name_owner,
        'property_owner_id_num' => $realEstate->property_owner_id_num,
        'unit_number_of_real' => $realEstate->unit_number_of_real,
        'property_owner_mobile' => $request->property_owner_mobile,
        'property_owner_iban' => $request->property_owner_iban,
        'name_real_estate' => $realEstate->name_real_estate,
        'number_of_floors' => $realEstate->number_of_floors,
        'property_type_id' => $realEstate->property_type_id,
        'property_usages_id' => $realEstate->property_usages_id,
        'property_type_name' => $realEstate->propertyType->name_ar,
        'property_usages_name' => $realEstate->propertyUsages->name_ar,
        'type_real_estate_other' => $realEstate->type_real_estate_other,
        'property_city_id' => $realEstate->property_city_id,
        'property_city_name' => $realEstate->tenantEntityCity->name_ar,  
        'property_place_id' => $realEstate->property_place_id,
        'property_place_name' => $realEstate->tenantEntityRegion->name_ar,  
        'street' => $realEstate->street,
        'neighborhood' => $realEstate->neighborhood,
        'building_number' => $realEstate->building_number,
        'postal_code' => $realEstate->postal_code,
        'extra_figure' => $realEstate->extra_figure,
        'user_id' => $realEstate->user_id,
        'step' => $realEstate->step,
        'is_deleted' => $realEstate->is_deleted,
    ];

    return response()->json([
        'message' => trans('api.updated_success'),
        'code' => 200,
        'success' => true,
        'data' => $realEstateData
    ]);
}



    public function delete($id){
    try {
        $realEstate = RealEstate::with('units')->findOrFail($id);

        if ($realEstate->units->isNotEmpty()) {
            foreach ($realEstate->units as $unit) {
                $unit->delete();
            }
        }

        $realEstate->delete();

        return $this->successMessage(trans('api.success'), 200);
    } catch (ModelNotFoundException $e) {
        return $this->successMessage(trans('api.not_found'), 404);
    }
    }
    



 
}