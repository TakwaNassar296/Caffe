<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContractResource;
use App\Http\Resources\SearchResource;
use App\Http\Traits\Responser;
use App\Models\Account;
use App\Models\BankAccount;
use App\Models\City;
use App\Models\Contract;
use App\Models\ContractPeriod;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Region;
use App\Models\ServicesPricing;
use App\Models\Setting;
use App\Models\UnitType;
use Flasher\Laravel\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

class ContractController extends Controller
{
    use Responser;



      /*
          * get all contract where is step > 6 && order desc 
          * in model contract deleted any contract not complete don't forget check it 
      */
       public function index(){
           
        $user = auth()->user();
        $contracts = Contract::where('user_id', $user->id)->orderBy('created_at','desc')->where('step','>','6')->where('is_delete',0)->paginate(10);
        
        $data = [];
        $data['data'] = ContractResource::collection($contracts);
        $data['pagination'] = $this->paginate($contracts);
        return $this->apiResponse($data, trans('api.success'));
    }

    public function show($id)
    {
        $user = auth()->user();

        $contract = Contract::where('user_id', $user->id)->findOrFail($id);

        return $this->apiResponse(new ContractResource($contract), trans('api.success'));
    }

   
    /*
     * In the last version, this function was very important.
     * In this version, it is not as important because the feature to complete incomplete contracts has been removed.
    */

    public function checkUncompletedContract()
    {
        $user = auth()->user();
                  $contract = Contract::where('user_id', $user->id)
                    ->where('is_completed', false)->where('is_delete', false)
                    ->latest('created_at')
                    ->first();

        if ($contract) {
            $data = [
                'check' => true,
                'contract_id'=>$contract->id,
                'step' => $contract->step,
            ];
            
        }
        
         else {
            $data = [
                'check' => false,
             ];
        }

        return $this->apiResponse($data, trans('api.success'));
    }


     public function contractType(Request $request)
     {
         $rules = [
             'contract_type' => 'required|in:housing,commercial',
             'real_id' => [
                 'nullable',
                 Rule::exists('real_estates', 'id'),
                 Rule::requiredIf($request->is_real == 1),
             ],
             'real_units_id' => [
                 'nullable',
                 Rule::exists('real_units', 'id'),
                 Rule::requiredIf($request->is_real == 1),
             ],
         ];
     
         $this->validate($request, $rules);
         $user = auth()->user();
     
         $data = [
             'contract_type' => $request->contract_type,
             'real_id' => $request->real_id,
             'real_units_id' => $request->real_units_id,
             'user_id' => $user->id,
             'step' => 1,
         ];
     
        
         $contract = Contract::create($data);
    
         if ($contract && isset($contract->id)) {
             $response = [
                 'message' => trans('api.success'),  
                 'code' => 200,
                 'success' => true,
                 'data' => [
                    'contract_id' => $contract->id,
                    'uuid' => (string) $contract->uuid,
                ],
             ];
         } else {
             $response = [
                 'message' => trans('api.failure'),  
                 'code' => 500,
                 'success' => false,
                 'data' => [
                    'id' => $contract ,
                 ],
             ];
         }
     
         return response()->json($response);
     }
     
     

    public function step1(Request $request)
    {
        $rules = [
            'id' => 'required|exists:contracts,id',
            
            'contract_ownership' => 'required|in:owner,tenant',
            'instrument_type' => 'nullable',
            'instrument_number' => 'required_if:instrument_type,electronic',
            'instrument_history' => 'required_if:instrument_type,electronic',
            'real_estate_registry_number' => 'required_if:instrument_type,strong_argument',
            'date_first_registration' => 'required_if:instrument_type,strong_argument',
            'property_type_id' => 'required|exists:rea_estat_types,id',
            'property_owner_is_deceased' => 'required|boolean',
            'number_of_floors'=>'required',
            'property_usages_id' => 'required_if:instrument_type,electronic,strong_argument', 
            'unit_number_of_real' => 'required_if:instrument_type,electronic,strong_argument|integer',   
        ];
        $this->validate($request, $rules);
        $contract = Contract::findOrFail($request->id);

       
        $data = [];
        $data['contract_ownership'] = $request->contract_ownership;
        $data['instrument_number'] = $request->instrument_number;
        $data['instrument_history'] = $request->instrument_history;
        $data['real_estate_registry_number'] = $request->real_estate_registry_number;
        $data['date_first_registration'] = $request->date_first_registration;
        $data['property_owner_is_deceased'] = $request->property_owner_is_deceased;
        $data['unit_number_of_real']=$request->unit_number_of_real;
        $data['instrument_type'] = $request->instrument_type ?? 'electronic';
        $data['property_type_id']=$request->property_type_id;
        $data['property_usages_id']=$request->property_usages_id;
        $data['number_of_floors']=$request->number_of_floors;
  
   
        if ($request->instrument_type == 'electronic') {
            $data['instrument_number'] = $request->instrument_number;
            $data['instrument_history'] = date('Y-m-d', strtotime($request->instrument_history));

         } 
        else if ($request->instrument_type == 'strong_argument') {
            $data['real_estate_registry_number'] = $request->real_estate_registry_number;
            $data['date_first_registration'] = $request->date_first_registration;
        }

        $data['step'] = 2;
        $contract->update($data);
     
        return response()->json([
            'message' => trans('api.success'),
            'code' => 200,
            'success' => true,
            'data' => [
                'contract_id' => $contract->id,
                'uuid' => (string) $contract->uuid,
            ],
        ]);   
    
    }

    public function step2(Request $request)
    {
        $contract = Contract::findOrFail($request->id);
    
        if ($contract->is_completed) {
            return $this->errorMessage(trans('api.completed_contract'));
        }
    
        $rules = [
            'id' => 'required|exists:contracts,id',
            'property_place_id' => 'required|integer|exists:regions,id',
            'property_city_id' => 'required|integer|exists:cities,id',
            'neighborhood' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'building_number' => 'required|string|max:50',
            'postal_code' => 'required|string|max:20',
            'extra_figure' => 'required|string|max:255',
        ];
    
        $this->validate($request, $rules);
    
        $city = City::where('id', $request->property_city_id)
                    ->where('region_id', $request->property_place_id)
                    ->first();
    
        if (!$city) {
            return $this->errorMessage(trans('api.city_not_include_region'));
        }
    
        $data = [
            'property_place_id' => $request->property_place_id,
            'property_city_id' => $request->property_city_id,
            'neighborhood' => $request->neighborhood,
            'street' => $request->street,
            'building_number' => $request->building_number,
            'postal_code' => $request->postal_code,
            'extra_figure' => $request->extra_figure,
            'step' => 3,
        ];
    
        $contract->update($data);
    
        return response()->json([
            'message' => trans('api.success'),
            'code' => 200,
            'success' => true,
            'data' => [
                'contract_id' => $contract->id,
                'uuid' => (string) $contract->uuid,
            ],
        ]);
    
    }
    
    public function step3(Request $request)
    {
        $contract = Contract::findOrFail($request->id);
    
        if ($contract->is_completed) {
            return $this->errorMessage(trans('api.completed_contract'));
        }
    
        $rules = [
        'name_owner' => 'required|string',
        'property_owner_id_num' => 'required|min:10',
        'property_owner_dob_hijri' => 'required', // Ensure the date format
        'property_owner_mobile' => 'required|min:10|regex:/^05[0-9]{8}$/',
        'property_owner_iban' => 'nullable|min:22',
        'add_legal_agent_of_owner'=>'required',
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
            'dob_hijri_of_property_owner_agent.required_if' => 'تاريخ ميلاد وكيل المالك مطلوب عند وجود وكيل.',
            'dob_hijri_of_property_owner_agent.date_format' => 'تاريخ ميلاد وكيل المالك يجب أن يكون بالشكل: يوم-شهر-سنة.',
            'mobile_of_property_owner_agent.required_if' => 'رقم جوال وكيل المالك مطلوب عند وجود وكيل.',
            'mobile_of_property_owner_agent.min' => 'رقم جوال وكيل المالك  يبدا ب 05 ويتبعه ثمانية ارقام',
            'mobile_of_property_owner_agent.regex' => 'رقم جوال وكيل المالك  يبدا ب 05 ويتبعه ثمانية ارقام',
            'agency_number_in_instrument_of_property_owner.required_if' => 'رقم الوكالة مطلوب عند وجود وكيل.',
            'agency_instrument_date_of_property_owner.required_if' => 'تاريخ الوكالة مطلوب عند وجود وكيل.',
            'agency_instrument_date_of_property_owner.date_format' => 'تاريخ الوكالة يجب أن يكون بالشكل: يوم-شهر-سنة.',
        ];
    
        $this->validate($request, $rules);
    
        $data = [
            'name_owner' => $request->name_owner,
            'property_owner_id_num' => $request->property_owner_id_num,
            'property_owner_dob_hijri' => $request->property_owner_dob_hijri,
            'property_owner_mobile' => $request->property_owner_mobile,
            'property_owner_iban' => $request->property_owner_iban,
            'add_legal_agent_of_owner'=>$request->add_legal_agent_of_owner,
            'id_num_of_property_owner_agent' => $request->id_num_of_property_owner_agent,
            'dob_hijri_of_property_owner_agent' => $request->dob_of_property_owner_agent,
            'mobile_of_property_owner_agent' => $request->mobile_of_property_owner_agent,
            'agency_number_in_instrument_of_property_owner' => $request->agency_number_in_instrument_of_property_owner,
            'agency_instrument_date_of_property_owner' => $request->agency_instrument_date_of_property_owner,
            'step' => 4,
        ];
    
     
        $contract->update($data);
    
        // Return a success response
        return response()->json([
            'message' => trans('api.success'),
            'code' => 200,
            'success' => true,
            'data' => [
                'contract_id' => $contract->id,
                'uuid' => (string) $contract->uuid,
            ],
        ]);

}


     public function step4(Request $request)
     {
         $contract = Contract::findOrFail($request->id);
       
         $rules = [
            'tenant_entity' => 'required|in:person,institution',
            'tenant_id_num' => 'nullable|required_if:tenant_entity,person|min:10',
            'tenant_dob_hijri' => 'nullable|required_if:tenant_entity,person',
            'tenant_mobile' => 'nullable|required_if:tenant_entity,person|min:10|regex:/^05[0-9]{8}$/',
            'region_of_the_tenant_legal_agent' => 'nullable|required_if:tenant_entity,institution|exists:regions,id',
            'city_of_the_tenant_legal_agent' => 'nullable|required_if:tenant_entity,institution|exists:cities,id',
            'tenant_entity_unified_registry_number' => 'nullable|required_if:tenant_entity,institution',
            'authorization_type' => 'nullable|required_if:tenant_entity,institution',
            'copy_of_the_authorization_or_agency' => 'nullable|required_if:authorization_type,agent_for_the_tenant|mimes:jpg,jpeg,png,pdf',
            'copy_of_the_owner_record' => 'nullable|mimes:jpg,jpeg,png,pdf',
            'id_num_of_property_tenant_agent' => 'nullable|min:10',
            'mobile_of_property_tenant_agent'=>'nullable',
            'dob_hijri_of_property_tenant_agent' => 'nullable|required_if:tenant_entity,institution',
        ];
    
        $messages = [
            'tenant_entity.required' => 'نوع الكيان المستأجر مطلوب.',
            'tenant_entity.in' => 'الكيان المستأجر يجب أن يكون شخص أو مؤسسة.',
            'tenant_id_num.required_if' => 'رقم الهوية مطلوب إذا كان الكيان المستأجر شخصاً.',
            'tenant_dob_hijri.required_if' => 'تاريخ ميلاد المستأجر مطلوب إذا كان الكيان شخصاً.',
            'tenant_mobile.required_if' => 'رقم الجوال مطلوب إذا كان الكيان المستأجر شخصاً.',
            'tenant_mobile.regex' => 'رقم الجوال يجب أن يبدأ بـ 05 ويكون مكون من 10 أرقام.',
            'authorization_type.required_if' => 'نوع التوكيل مطلوب إذا كان الكيان مؤسسة.',
            'id_num_of_property_tenant_agent.min' => 'رقم الهوية لا يقل عن عشرة أرقام.',
            'dob_hijri_of_property_tenant_agent.required_if' => 'تاريخ ميلاد وكيل المالك مطلوب.',
            'copy_of_the_owner_record.mimes' => 'نسخة السجل يجب أن تكون بصيغة jpg, jpeg, png, أو pdf.',
            'copy_of_the_authorization_or_agency.required_if' => 'نسخة من التوكيل مطلوبة.',
            'copy_of_the_authorization_or_agency.mimes' => 'نسخة التوكيل يجب أن تكون بصيغة jpg, jpeg, png, أو pdf.',
        ];
    
        $validatedData = $request->validate($rules, $messages);
    
        if ($request->hasFile('copy_of_the_owner_record')) {
            $validatedData['copy_of_the_owner_record'] = $request->file('copy_of_the_owner_record')->store('copy_of_the_owner_record', 'public');
        }
    
        if ($request->hasFile('copy_of_the_authorization_or_agency')) {
            $validatedData['copy_of_the_authorization_or_agency'] = $request->file('copy_of_the_authorization_or_agency')->store('authorizations', 'public');
        }
    
        $data = array_merge($validatedData, [
            'step' => 5,
            'copy_of_the_owner_record' => $validatedData['copy_of_the_owner_record'] ?? $contract->copy_of_the_owner_record,
            'copy_of_the_authorization_or_agency' => $validatedData['copy_of_the_authorization_or_agency'] ?? $contract->copy_of_the_authorization_or_agency,
        ]);
    

        $contract->update($data);
    
        return response()->json([
            'message' => trans('api.success'),
            'code' => 200,
            'success' => true,
            'data' => [
                'contract_id' => $contract->id,
                'uuid' => (string) $contract->uuid,
            ],
        ]);
    }
    
    public function step5(Request $request)
    {
        $rules = [
            
            'id' => 'required|exists:contracts,id',
            'unit_type_id' => 'required|exists:unit_types,id',
            'unit_usage_id' => 'required|exists:unit_usage,id',
            'unit_number' => 'required|string|max:255',
            'floor_number' => 'required|integer',
            'unit_area' => 'required|numeric',
            'tootal_rooms' => 'nullable|integer',
            'The_number_of_halls' => 'nullable|integer',
            'The_number_of_kitchens' => 'nullable|integer',
            'The_number_of_toilets' => 'nullable|integer',
            'window_ac' => 'required|integer',
            'split_ac' => 'required|integer',
            'electricity_meter_number' => 'nullable|string|max:255',
            'water_meter_number' => 'nullable|string|max:255',
        ];
        
        $this->validate($request, $rules);
        
        $contract = Contract::findOrFail($request->id);
        
        // Prepare data for update
        $data = [
            'step' => 6,
            'unit_type_id' => $request->unit_type_id,
            'unit_usage_id' => $request->unit_usage_id,
            'unit_number' => $request->unit_number,
            'floor_number' => $request->floor_number,
            'unit_area' => $request->unit_area,
            'tootal_rooms' => $request->tootal_rooms,
            'The_number_of_halls' => $request->The_number_of_halls,
            'The_number_of_kitchens' => $request->The_number_of_kitchens,
            'The_number_of_toilets' => $request->The_number_of_toilets,
            'window_ac' => $request->window_ac,
            'split_ac' => $request->split_ac,
            'electricity_meter_number' => $request->electricity_meter_number,
            'water_meter_number' => $request->water_meter_number,
            'app_or_web'=>'app',
        ];
        
        $contract->update($data);
        
        return response()->json([
            'message' => trans('api.success'),
            'code' => 200,
            'success' => true,
            'data' => [
                'contract_id' => $contract->id,
                'uuid' => (string) $contract->uuid,
            ],
        ]);

}
    
    public function step6(Request $request)
    {
        $rules = [
            'id' => 'required|exists:contracts,id',
            'contract_starting_date' => 'required|date',
            'contract_term_in_years' => 'required|exists:contract_periods,id',
            'annual_rent_amount_for_the_unit' => 'required|numeric',
            'payment_type_id' => 'required|exists:payment_types,id',
            'conditions' => 'required|boolean',
            'other_conditions' => 'required_if:conditions,1|string|max:255',
        ];
    

        $messages = [
            'other_conditions.required_if' => 'حقل شروط أخرى مطلوب عندما تكون الشروط مفعلة.',
        ];
    
        $this->validate($request, $rules, $messages);    
        $contract = Contract::findOrFail($request->id);
    
        if ($contract->is_completed) {
            return $this->errorMessage(trans('api.completed_contract'));
        }
    
        $data = [
            'contract_starting_date' => date('Y-m-d', strtotime($request->contract_starting_date)),
            'contract_term_in_years' => $request->contract_term_in_years,
            'annual_rent_amount_for_the_unit' => $request->annual_rent_amount_for_the_unit,
            'payment_type_id' => $request->payment_type_id,
            'step'=>7,
        ];
    
        if ($request->filled('other_conditions')) {
            $data['other_conditions'] = $request->other_conditions;
        }
    
        if ($request->filled('daily_fine')) {
            $data['daily_fine'] = $request->daily_fine;
        }
    
        $contract->update($data);
    
        return response()->json([
            'message' => trans('api.success'),
            "code" => 200,
            "success" => true,
            'data' => [
                'contract_id' => $contract->id,
                'uuid' => (string) $contract->uuid,
                'price_contract_term' => $contract->contractTermInYears->price ?? null,
            ],
        ]);
    }
    
       /*
          * function get data contract
          * para uuuid contract 
          * return all contract where file is not null and return it file when it paid
      */
   
      public function getContracts($uuid)
      {
          $user_id = Auth::id();
          $contracts = Contract::where('uuid', $uuid)->whereNotNull('file')->get();
          $files = [];
      
          foreach ($contracts as $contract) {
              $filePath = getFilePath($contract->file);
               $filePath = str_replace('public/', '', $filePath);
              
              $files[] = [
                  'file' => $filePath,
                  'created_at' => $contract->created_at,
                  'user' => $contract->user_id,
                  'contract_uuid' => $contract->uuid,
              ];
          }
      
          if (!empty($files)) {
              return $this->apiResponse(['files' => $files], trans('api.success'));
          } else {
              return $this->apiResponse(null, trans('api.waitContract'));
          }
      }
    
      public function search($searchTerm)
      {
          // Search for contracts that match either the tenant_id_num or property_owner_id_num using 'like'
          $contracts = Contract::where('tenant_id_num', 'like', '%' . $searchTerm . '%')
                               ->orWhere('property_owner_id_num', 'like', '%' . $searchTerm . '%')
                               ->get();
          
          // Check if contracts were found
          if ($contracts->isEmpty()) {
              return $this->apiResponse(null, trans('api.error'));   
          }
      
          
          $data = SearchResource::collection($contracts);   
          return $this->apiResponse($data, trans('api.success'));
      }
      
      /*
          * finanacial return all details financial contract 
          * get services and contract period 
          * calc if exist coupon or not 
      */
      public function financial(Request $request, $uuid)
      {
        $user = Auth::user();
        $user_id = $user->id;
    
        // Find the contract
        $contract = Contract::where('uuid', $uuid)->where('user_id', $user_id)->first();
        if (!$contract) {
            return response()->json([
                'message' => 'العقد غير موجود',
                'success' => false,
                'data' => [],
            ], 404);
        }
    
        // Fetch the service pricing
        $pricing = ServicesPricing::where('contract_type', $contract->contract_type)->get();
        $totalPricing = $pricing->sum('price');
    
        // Calculate the contract price
        $contractCoupon = CouponUsage::where('contract_uuid', $contract->uuid)->first();
        $totalContractPrice = $contractCoupon 
            ? $contractCoupon->calculateDiscountedPrice($contract) 
            : ($contract->getPriceContractAttribute() + $totalPricing);
    
        // Fetch contract period price
        $contractPeriodPrice = ContractPeriod::where('contract_type', $contract->contract_type)->where('id', $contract->contract_term_in_years)->value('price') ?? 0;
    
     
       
       
 
        // Application fees and tax
        $setting = Setting::first();
        $app_fees = $setting ? intval($setting->application_fees) : 0;
    
        $tax = $setting 
            ? ($contract->contract_type === 'housing' ? intval($setting->housing_tax) : intval($setting->commercial_tax)) 
            : 0;
    
        
        $priceDetails = [
            'contract_period_price' => $contractPeriodPrice,
            'application_fees' => $app_fees,
            'tax' => $tax,
        ];
    
        
        $services = $pricing->map(function ($service) {
            return [
                'service_name' => $service->name_ar,
                'service_price' => $service->price,
            ];
        })->toArray();
    
        // Calculate final price
        $finalContractPrice = $totalContractPrice + $contractPeriodPrice;
    
        // Calculate coupon discount if present
        $couponAmount = 0;
        if ($contractCoupon) {
            $coupon = Coupon::where('id', $contractCoupon->coupon_id)->first();
            if ($coupon) {
                if ($coupon->type_coupon === 'ratio') {
                    $couponAmount = ($totalContractPrice * $coupon->value_coupon / 100);
                } else {
                    $couponAmount = $coupon->value_coupon;
                }
            }
        }
    
       
        $responseData = [
            'price_details' => $priceDetails,
            'services' => $services,
            'total_price' => $finalContractPrice+$couponAmount,
        ];
    
        if ($contractCoupon) {
            $responseData['coupon'] = $couponAmount;
            $responseData['total_price_after_coupon'] = $finalContractPrice ;
        }
    
        return response()->json([
            'status' => 'success',
            'message' => 'التفاصيل الماليه',
            'data' => $responseData,
        ], 200);
    }

      
}