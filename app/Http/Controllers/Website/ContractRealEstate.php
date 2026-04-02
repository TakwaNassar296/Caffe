<?php

namespace App\Http\Controllers\Website;

use \Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\Step1Request;
use App\Http\Requests\Step2Request;
use App\Http\Requests\step3Request;
use App\Models\Account;
use App\Models\BankAccount;
use App\Models\City;
use App\Models\Contract;
use App\Models\ContractPeriod;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Paperwork;
use App\Models\PaymentType;
use App\Models\ReaEstatType;
use App\Models\ReaEstatUsage;
use App\Models\RealEstate;
use App\Models\Region;
use App\Models\ServicesPricing;
use App\Models\Setting;
use App\Models\UnitsReal;
use App\Models\UnitType;
use App\Models\UsageUnit;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ContractRealEstate extends Controller
    {
    
    
    public function unitReal($uuid, $real_id)
    {
        $userReal = RealEstate::findOrFail($real_id);
            $user_id = Auth::user()->id;
            $contract = Contract::where('uuid', $uuid)
                ->where('user_id', $user_id)
                ->firstOrFail();
            $units = UnitsReal::where('real_estates_units_id', $userReal->id)->paginate(6);
           
        return view('website.units.index', compact('contract', 'real','userReal','units'));
    }
     
    
    /* 
    this function created a contract by unit  
    */
     public function createContractUnit($id)
     {
          
         $unit = UnitsReal::find($id);
         
          
         if (!$unit) {
             return redirect()->back()->withErrors(['Unit not found']);
         }
     
         $realEstate = $unit->realEstate;  
         $real_id = $unit->real_estates_units_id;
     
        
         $contract = new Contract();
         $contract->user_id = Auth::user()->id;  
         $contract->real_id = $real_id;
         $paperWorks=Paperwork::all();
        
         if (!isset($contract->uuid)) {
             $contract->uuid = (string) Str::uuid();  
         }
     
         
         if ($contract->save()) {
            return redirect()->route('real.paperwork', [
                'uuid' => $contract->uuid,
                'id' => $unit->id,
                'real_id' => $real_id,
            ]); 
         }
     
          
         return redirect()->back()->withErrors(['فشل حفظ العقد']);
     }
     
    
    
    public function contractCreate($real_id)
    {
         $user_id = Auth::user()->id;
        $real = RealEstate::find($real_id);
        $item= RealEstate::find($real_id);
         if (!$real) {
            return redirect()->back()->withErrors('Real estate not found.');
        }
    
         $units = [];  
        return view('website.RealEstate.index', compact('units', 'item','real'));
    }
    
    /*
     this is a step one in contract have real estate 
     choose a contract type and submit
    */
    
    
    
    public function submitContractType(Request $request, $real_id)
    {
        try{
    
        $real = RealEstate::findOrFail($real_id);
        $contractTypeValue = $real->contract_type ??'housing';
        $contract = new Contract();
        $contract->user_id = Auth::user()->id;
        $contract->real_id = $real->id;
        $contract->contract_type = $contractTypeValue;
    
        $contract->save();
        return redirect()->route('real.units', ['uuid' => $contract->uuid, 'real_id' => $real->id]);
    
        } catch (ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'العقار غير موجود');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ ما حاول مره اخري');
        }
    
    }
    
    
    public function unitRealContract($uuid, $real_id)
    {
      
           $userReal = RealEstate::findOrFail($real_id);
            $user_id = Auth::user()->id;
            $contract = Contract::where('uuid', $uuid)
                ->where('user_id', $user_id)
                ->firstOrFail();
            $units = UnitsReal::where('real_estates_units_id', $userReal->id)->paginate(6);
     
        
         return view('website.units.unitRealContract', compact('units', 'contract', 'userReal'));
    }
    
    
    public function submitUnits($uuid, $real_id, $id)
    {
         $real = RealEstate::findOrFail($real_id);
        $unit = UnitsReal::findOrFail($id);
        $contract = Contract::where('uuid', $uuid)->firstOrFail();
    
         if ($contract && $unit) {
             $contract->update([
            'real_units_id' => $unit->id,
            'step' => 1,
        ]);
        }
    
         return redirect()->route('real.paperwork', [
            'uuid' => $uuid,
            'id' => $unit->id,
            'real_id' => $real_id,
        ]);
    }
    
    
    public function paperwork($uuid, $real_id, $id)
    {
        $real = RealEstate::findOrFail($real_id);
        $units = UnitsReal::findOrFail($id);
        $contract = Contract::where('uuid', $uuid)->where('user_id', Auth::user()->id)->firstOrFail();
       
        $paperWorks = Paperwork::where('contract_type', $contract->contract_type)->get();
       
        $step = $contract->step;
        return view('website.contractReal.work_paper', compact('paperWorks', 'contract', 'units', 'real'));
    }
       
        public function RealStep1(Request $request, $uuid, $real_id, $id)
        {    
          try {
            $setting=Setting::first();
            $value_contract=Account::first();
            $user_id = Auth::user()->id;
            $contract = Contract::where('uuid', $uuid)->where('user_id', $user_id)->first();
            $pricing = $contract->services;
            $unit = UnitsReal::findOrFail($id);
            $step = $contract->step;
            $regions = Region::all();
            $real = RealEstate::findOrFail($real_id);     
            $realTypes = ReaEstatType::where('contract_type', $real->contract_type)->get();
            $usages = ReaEstatUsage::where('contract_type', $real->contract_type)->get();
            $step = $contract->step;
         
          

            return view('website.contractReal.step1', compact('contract','setting', 'value_contract','usages','realTypes', 'regions', 'unit', 'real'));
            
             
    
        } catch (\Exception $e) {
            return redirect()->back();
        }
    
      }
    
        public function submitStep1(Request $request, $uuid, $real_id, $id)
        {
    
                $validatedData = $request->validate([
                    'contract_ownership' => 'required',  
                    'property_owner_is_deceased' => 'required', 
                    'instrument_type' => 'nullable|in:electronic,strong_argument|required_if:property_owner_is_deceased,1',  
                    'instrument_number' => 'nullable|string|max:255|required_if:instrument_type,electronic',  
                    'instrument_history' => 'nullable|date|required_if:instrument_type,electronic',  
                    'date_first_registration' => 'nullable|date|required_if:instrument_type,strong_argument',  
                    'real_estate_registry_number' => 'nullable|string|required_if:instrument_type,strong_argument',
                    'property_type_id' => 'required_if:instrument_type,electronic,strong_argument', 
                    'property_usages_id' => 'required_if:instrument_type,electronic,strong_argument', 
                    'number_of_floors' => 'required_if:instrument_type,electronic,strong_argument|integer',  
                    'unit_number_of_real' => 'required_if:instrument_type,electronic,strong_argument|integer',  
                ], 
            
                [
                    'contract_ownership.required' => 'اختر صاحب العقار',
                    'property_owner_is_deceased.required' => 'اختر حالة مالك العقار (هل هو متوفى؟)',
                    'instrument_type.required_if' => 'اختر نوع الصك في حالة العقار المتوفي',
                    'instrument_type.in' => 'نوع الصك غير صالح',
                    'instrument_number.required_if' => 'رقم الصك مطلوب في حالة الصك الإلكتروني',
                    'instrument_number.string' => 'رقم الصك يجب أن يكون نصاً',
                    'instrument_history.required_if' => 'تاريخ الصك مطلوب في حالة الصك الإلكتروني',
                    'instrument_history.date' => 'تاريخ الصك غير صالح',
                    'date_first_registration.required_if' => 'تاريخ التسجيل الأول مطلوب في حالة الصك السجل العقاري',
                    'date_first_registration.date' => 'تاريخ التسجيل الأول غير صالح',
                    'real_estate_registry_number.required_if' => 'رقم السجل العقاري مطلوب في حالة الصك السجل العقاري',
                    'real_estate_registry_number.string' => 'رقم السجل العقاري يجب أن يكون نصاً',
                    'property_type_id.required_if' => 'اختر نوع العقار في حالة الصك الإلكتروني أو السجل العقاري',
                    'property_usages_id.required_if' => 'اختر استخدام العقار في حالة الصك الإلكتروني أو السجل العقاري',
                    'number_of_floors.required_if' => 'عدد الطوابق مطلوب في حالة الصك الإلكتروني أو السجل العقاري',
                    'number_of_floors.integer' => 'عدد الطوابق يجب أن يكون رقماً صحيحاً',
                    'unit_number_of_real.required_if' => 'عدد الوحدات مطلوب في حالة الصك الإلكتروني أو السجل العقاري',
                    'unit_number_of_real.integer' => 'عدد الوحدات يجب أن يكون رقماً صحيحاً',
                ]);
     
            try {
            
                $real = RealEstate::with('contracts')->findOrFail($real_id);
                $unit = UnitsReal::findOrFail($id);
    
                $user_id = Auth::user()->id;
                 $contract = Contract::where('uuid', $uuid)->where('user_id', $user_id)->firstOrFail();
                 $data = [
                    'contract_ownership' => $request->contract_ownership,
                    'property_owner_is_deceased' => $request->property_owner_is_deceased,
                    'instrument_type' => $request->instrument_type,
                    'property_type_id' => $request->property_type_id,
                    'property_usages_id' => $request->property_usages_id,
                    'building_number' => $request->building_number,
                    'postal_code' => $request->postal_code,
                    'unit_number_of_real'=>$request->unit_number_of_real,
                    'number_of_floors' => $request->number_of_floors,
                    'step' => 2,  
                ];
        
                 
                if ($request->instrument_type == 'electronic') {
                    $data['instrument_number'] = $request->instrument_number;
                    $data['instrument_history'] = $request->instrument_history ? date('Y-m-d', strtotime($request->instrument_history)) : null;
                } elseif ($request->instrument_type == 'strong_argument') {
                    $data['date_first_registration'] = $request->date_first_registration;
                    $data['real_estate_registry_number'] = $request->real_estate_registry_number;
                }
        
                 $contract->update($data);
                 return redirect()->route('real.step2',['uuid' => $uuid, 'real_id' => $real->id, 'id' => $unit->id])->with('success', 'تم اتمام الخطوه الأولي بنجاح.');
         
            } catch (\Exception $e) {
                 return redirect()->back()->with('error', 'للاأسف حدث خطأ ما يرجي المحاوله في وقت اخر ' . $e->getMessage());
            }
        }
        
        public function PageStep2($uuid, $real_id, $id)
        {
            $unit = UnitsReal::findOrFail($id);
            $real = RealEstate::with('contracts')->findOrFail($real_id);
            
             try {
                $user_id = Auth::user()->id;
                $contract = Contract::where('uuid', $uuid)->where('user_id', $user_id)->first();
                $regions = Region::all();
                $city = City::all();
                $step = $contract->step;
    
               return view('website.contractReal.step2', compact('regions','city','contract', 'unit', 'real'));
                 
            } catch (\Exception $e) {
                return redirect()->back();
            }
        }
    
    
        public function submitStep2(Request $request, $uuid, $real_id, $id)
        {
          
            try {
                $rules = [
                    'property_place_id' => 'required|integer|exists:regions,id', 
                    'property_city_id' => 'required|integer|exists:cities,id',  
                    'neighborhood' => 'required|string|max:255',
                    'street' => 'required|string|max:255',
                    'building_number' => 'required|string|max:50',
                    'postal_code' => 'required|string|max:20',
                    'extra_figure' => 'required|string|max:255',  
                ];
            
                // Define custom validation messages
                $messages = [
                    'property_place_id.required' => 'حقل مكان العقار مطلوب.',
                    'property_place_id.integer' => 'حقل مكان العقار يجب أن يكون رقماً.',
                    'property_place_id.exists' => 'مكان العقار غير موجود.',
                    'property_city_id.required' => 'حقل مدينة العقار مطلوب.',
                    'property_city_id.integer' => 'حقل مدينة العقار يجب أن يكون رقماً.',
                    'property_city_id.exists' => 'مدينة العقار غير موجودة.',
                    'neighborhood.required' => 'حقل الحي مطلوب.',
                    'neighborhood.string' => 'حقل الحي يجب أن يكون نصاً.',
                    'neighborhood.max' => 'حقل الحي يجب ألا يتجاوز 255 حرفاً.',
                    'street.required' => 'حقل الشارع مطلوب.',
                    'street.string' => 'حقل الشارع يجب أن يكون نصاً.',
                    'street.max' => 'حقل الشارع يجب ألا يتجاوز 255 حرفاً.',
                    'building_number.required' => 'حقل رقم المبنى مطلوب.',
                    'building_number.string' => 'حقل رقم المبنى يجب أن يكون نصاً.',
                    'building_number.max' => 'حقل رقم المبنى يجب ألا يتجاوز 50 حرفاً.',
                    'postal_code.required' => 'حقل الرمز البريدي مطلوب.',
                    'postal_code.string' => 'حقل الرمز البريدي يجب أن يكون نصاً.',
                    'postal_code.max' => 'حقل الرمز البريدي يجب ألا يتجاوز 20 حرفاً.',
                    'extra_figure.string' => 'حقل الرقم الإضافي يجب أن يكون نصاً.',
                    'extra_figure.max' => 'حقل الرقم الإضافي يجب ألا يتجاوز 255 حرفاً.',
                ];
                $unit = UnitsReal::findOrFail($id);
                $real = RealEstate::with('contracts')->findOrFail($real_id);
                $user_id = Auth::user()->id;
                $contract = Contract::where('uuid', $uuid)->where('user_id', $user_id)->first();
                $data = [];
                $data = [
                    'property_place_id' => $request->input('property_place_id'),
                    'property_city_id' => $request->input('property_city_id'),
                    'neighborhood' => $request->input('neighborhood'),
                    'street' => $request->input('street'),
                    'building_number' => $request->input('building_number'),
                    'postal_code' => $request->input('postal_code'),
                    'extra_figure' => $request->input('extra_figure'),
                    'step' => 3,
                ];
                $contract->update($data);
    
                return redirect()->route('real.contract.step3', ['uuid' => $uuid, 'real_id' => $real->id, 'id' => $unit->id])->with('success', 'تم اتمام الخطوه الثانية بنجاح.');
            } catch (\Exception $e) {
                return redirect()->back();
            }
        }
        public function contract_step3($uuid, $real_id, $id)
        {
                $unit = UnitsReal::findOrFail($id);
                $real = RealEstate::with('contracts')->findOrFail($real_id);
          
                $user_id = Auth::user()->id;
                $contract = Contract::where('uuid', $uuid)->where('user_id', $user_id)->first();
                $unit_real=UnitsReal::with('contracts')->findOrFail($id);
                $unit_usage = UsageUnit::where('contract_type', $contract->contract_type)->get();
                $unit_type = UnitType::where('contract_type', $contract->contract_type)->get();
                $units = UnitType::all();
                $step = $contract->step;
              return view('website.contractReal.step3Real', compact('unit_real', 'unit_real', 'unit_usage', 'units','unit_type', 'contract', 'unit', 'real'));
        }
    
    
     public function submitStep3(Request $request, $uuid, $real_id, $id)
        {
    
     
             $rules = [
                'name_owner' => 'required|string',
                'property_owner_id_num'=>'required|min:10',
                'property_owner_dob_hijri' => 'required',
                'property_owner_mobile' => 'required|min:10|regex:/^05[0-9]{8}$/',
                'property_owner_iban' => 'required|min:22',
                'id_num_of_property_owner_agent' => 'required_if:add_legal_agent_of_owner,1',
                'dob_hijri_of_property_owner_agent' => 'required_if:add_legal_agent_of_owner,1',
                'mobile_of_property_owner_agent' => 'required_if:add_legal_agent_of_owner,1',
                'agency_number_in_instrument_of_property_owner' => 'required_if:add_legal_agent_of_owner,1',
                'agency_instrument_date_of_property_owner' => 'required_if:add_legal_agent_of_owner,1',
            ];
        
            // Define custom validation messages in Arabic
            $messages = [
                'name_owner.required' => 'اسم المالك مطلوب.',
                'national_num.required' => 'رقم هوية المالك مطلوب.',
                'national_num.min' => 'رقم هوية المالك لا يقل عن عشرة أرقام.',
                'property_owner_mobile.required' => 'رقم جوال المالك مطلوب.',
                'property_owner_mobile.min' => 'رقم جوال المالك 10 ارقام علي الاقل.',
                'property_owner_mobile.regex' => 'رقم جوال المالك  يبدا ب 05 ويتبعه ثمانية ارقام',
                'property_owner_dob_hijri.required' => 'تاريخ ميلاد المالك مطلوب.',
                'property_owner_iban.required' => 'رقم الآيبان الخاص بالمالك مطلوب.',
                'property_owner_iban.min' => 'رقم الآيبان الخاص بالمالك لا يقل عن 22 رقم.',
                'agent_iban_of_property_owner.required_if' => 'رقم الآيبان لوكيل المالك مطلوب عند وجود وكيل.',
                'id_num_of_property_owner_agent.required_if' => 'رقم هوية وكيل المالك مطلوب عند وجود وكيل.',
                'dob_hijri_of_property_owner_agent.required_if' => 'تاريخ ميلاد وكيل المالك مطلوب عند وجود وكيل.',
                'mobile_of_property_owner_agent.required_if' => 'رقم جوال وكيل المالك مطلوب عند وجود وكيل.',
                'agency_number_in_instrument_of_property_owner.required_if' => 'رقم الوكالة مطلوب عند وجود وكيل.',
                'agency_instrument_date_of_property_owner.required_if' => 'تاريخ الوكالة مطلوب عند وجود وكيل.',
             ];
             // Validate the request
            //  try {
                $unit = UnitsReal::findOrFail($id);
                $real = RealEstate::with('contracts')->findOrFail($real_id);
    
                $user_id = Auth::user()->id;
                $contract = Contract::where('uuid', $uuid)->where('user_id', $user_id)->first();
                $data = [
                    'name_owner' => $request['name_owner'],
                    'real_units_id'=>$id,
                    'national_num' => $request['national_num'],
                    'property_owner_dob_hijri' => $request['property_owner_dob_hijri'],
                    'property_owner_mobile' => $request['property_owner_mobile'],
                    'property_owner_iban' => $request['property_owner_iban'],
                    'agent_iban_of_property_owner' => $request['agent_iban_of_property_owner'] ?? null,
                    'id_num_of_property_owner_agent' => $request['id_num_of_property_owner_agent'] ?? null,
                    'dob_hijri_of_property_owner_agent' => $request['dob_hijri_of_property_owner_agent'] ?? null,
                    'mobile_of_property_owner_agent' => $request['mobile_of_property_owner_agent'] ?? null,
                    'agency_number_in_instrument_of_property_owner' => $request['agency_number_in_instrument_of_property_owner'] ?? null,
                    'agency_instrument_date_of_property_owner' => $request['agency_instrument_date_of_property_owner'] ?? null,
                    'step' => 4,
                ];
        
                // Update the contract
                $contract->update($data);
                 return redirect()->route('real.contract.step4', ['uuid' => $uuid, 'real_id' => $real->id, 'id' => $unit->id]);
            
        }
    
        public function contract_step4($uuid, $real_id, $id)
        {
            
            $unit = UnitsReal::where('id', $id)->first();
            $user_id = Auth::user()->id;
            $regions = Region::all();
            $contract = Contract::where('uuid', $uuid)->where('user_id', $user_id)->firstOrFail();
            $real = RealEstate::findOrFail($real_id);     
            $step = $contract->step;

            return view('website.contractReal.step4', compact('regions', 'unit', 'real', 'contract'));
            
        }
         
    
        public function submitStep4(Request $request, $uuid, $real_id, $id){
        
                $user_id = Auth::user()->id;
                $real = RealEstate::with('contracts')->find($real_id);
                $contract = Contract::where('uuid', $uuid)->where('user_id', $user_id)->first();
                $unit = UnitsReal::where('id', $id)->first();
                $data=UnitsReal::where('real_estates_units_id ',$real->id);
                $validatedData = $request->validate([
                    'tenant_entity' => 'required|in:person,institution',
                    'tenant_id_num' => 'nullable|required_if:tenant_entity,person|min:10',
                    'tenant_dob_hijri' => 'nullable|required_if:tenant_entity,person',
                    'tenant_mobile' => 'nullable|required_if:tenant_entity,person|min:10|regex:/^05[0-9]{8}$/',
                    'region_of_the_tenant_legal_agent' => 'nullable|required_if:tenant_entity,institution|exists:regions,id',
                    'city_of_the_tenant_legal_agent' => 'nullable|required_if:tenant_entity,institution|exists:cities,id',
                    'tenant_entity_unified_registry_number' => 'nullable|required_if:tenant_entity,institution',
                    'authorization_type' => 'nullable|required_if:tenant_entity,institution',
                    'copy_of_the_authorization_or_agency' => 'nullable',
                    'copy_of_the_owner_record' => 'nullable',
                    'id_num_of_property_tenant_agent' => 'nullable|min:10',
                    'mobile_of_property_tenant_agent' => 'nullable',
                    'dob_hijri_of_property_tenant_agent' => 'nullable|required_if:tenant_entity,institution',
                ], [
                    'tenant_entity.required' => 'نوع الكيان المستأجر مطلوب.',
                    'tenant_entity.in' => 'الكيان المستأجر يجب أن يكون شخص أو مؤسسة.',
                    'tenant_id_num.required_if' => 'رقم الهوية مطلوب إذا كان الكيان المستأجر شخصاً.',
                    'tenant_dob_hijri.required_if' => 'تاريخ ميلاد المستأجر مطلوب إذا كان الكيان شخصاً.',
                    'tenant_mobile.required_if' => 'رقم الجوال مطلوب إذا كان الكيان المستأجر شخصاً.',
                    'tenant_mobile.regex' => 'رقم الجوال يجب أن يبدأ بـ 05 ويكون مكون من 10 أرقام.',
                    'authorization_type.required_if' => 'نوع التوكيل مطلوب إذا كان الكيان مؤسسة.',
                    'city_of_the_tenant_legal_agent.requied_if'=>'المدينه مطلوبه',
                    'region_of_the_tenant_legal_agent.requied_if'=>'المنطقه مطلوبه',
                    'id_num_of_property_tenant_agent.min' => 'رقم الهوية لا يقل عن عشرة أرقام.',
                    'dob_hijri_of_property_tenant_agent.required_if' => 'تاريخ ميلاد وكيل المالك مطلوب.',
                    'copy_of_the_owner_record.mimes' => 'نسخة السجل يجب أن تكون بصيغة jpg, jpeg, png, أو pdf.',
                    'copy_of_the_authorization_or_agency.required_if' => 'نسخة من التوكيل مطلوبة.',
                    'copy_of_the_authorization_or_agency.mimes' => 'نسخة التوكيل يجب أن تكون بصيغة jpg, jpeg, png, أو pdf.',
                ]);

                // Handle file uploads
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
                if ($request->tenant_entity === 'person' && $request->filled('tenant_dob_hijri')) {
                    $dob = $request->input('tenant_dob_hijri');
                    $dateParts = explode('-', $dob);
                    if (count($dateParts) === 3) {
                        try {
                            $birthDate = \Carbon\Carbon::createFromFormat('d-m-Y', $dob);
                            $age = now()->diffInYears($birthDate);

                            if ($age < 18) {
                                return redirect()->back()->withErrors(['tenant_dob_hijri' => 'يجب أن يكون عمر المستأجر 18 عامًا على الأقل.'])->withInput();
                            }
                        } catch (\Exception $e) {
                            return redirect()->back()->withErrors(['tenant_dob_hijri' => 'تاريخ الميلاد غير صالح.'])->withInput();
                        }
                    }
                }

                $contract->update($data);
               return redirect()->route('real.contract.step5', ['uuid' => $uuid, 'real_id' => $real->id, 'id' => $unit->id]);
        
    }
    public function contract_step5($uuid, $real_id, $id)
    {
        try {
            $user_id = Auth::user()->id;
            $contract = Contract::where('uuid', $uuid)->where('user_id', $user_id)->first();
            $real = RealEstate::with('contracts')->findOrFail($real_id);
    
            if (!$contract) {
                return redirect()->back()->with('error', 'Contract not found.');
            }
    
            // Assuming you need to fetch the unit based on `id`
            $unit = UnitsReal::where('id', $id)->firstOrFail();
             if (!$unit) {
                return redirect()->back()->with('error', 'Unit not found.');
            }
    
            $unitUsage = UsageUnit::all();
            $unitType = UnitType::all();
            $step = $contract->step;
           
       
            return view('website.contractReal.step5', compact('unitType', 'real', 'unitUsage', 'contract', 'unit'));
           
                
        
        } catch (\Exception $e) {
              return redirect()->back()->with('error', 'حدث خطأ ما حاول مره اخري');
        }
    }
    
      public function submitRealStep5(Request $request, $uuid, $real_id, $id)
        {
            $user_id = Auth::user()->id;
        
             $real = RealEstate::with('contracts')->findOrFail($real_id);
            $contract = Contract::where('uuid', $uuid)->where('user_id', $user_id)->first();
            if (!$contract) {
                return redirect()->back()->with('error', 'Contract not found.');
            }
        
             $unit = Contract::where('real_units_id', $contract->real_units_id)->first();
        
             $validatedData = $request->validate([
                'unit_type_id' => 'required|exists:unit_types,id',
                'unit_usage_id' => 'required|exists:unit_usage,id',
                'unit_number' => 'required|string|max:255',
                'floor_number' => 'required|integer|min:1|max:10',
                'unit_area' => 'required',
                'tootal_rooms' => 'nullable|integer|min:1|max:10',
                'The_number_of_halls' => 'nullable|integer|min:1|max:10',
                'The_number_of_kitchens' => 'nullable|integer|min:1|max:10',
                'The_number_of_toilets' => 'nullable|integer|min:1|max:10',
                'window_ac' => 'nullable|integer|min:0|max:10',
                'split_ac' => 'nullable|integer|min:0|max:10',
                'electricity_meter_number' => 'nullable|string|max:255',
                'water_meter_number' => 'nullable|string|max:255',
            ]);
            //  try {
                // Update contract data with validated values
                $contract->update($validatedData);
                $contract->update(['step' => 6]);
        
                // Redirect to step 6
                return redirect()->route('real.submit.step6', [
                    'uuid' => $uuid,
                    'real_id' => $real->id,
                    'id' => $unit->id
                ]);
            // } catch (\Exception $e) {
            //     return redirect()->back()->with('error', 'حدث خطأ ما حاول مره اخري');
            // }
}

        
        public function contract_step6($uuid, $real_id, $id)
        {
            // try {
                $user_id = Auth::user()->id;
                $contract = Contract::where('uuid', $uuid)->where('user_id', $user_id)->first();
                $real=Contract::where('real_id',$real_id)->first();
                $unit = Contract::where('real_units_id',$contract->real_units_id)->first();
         
               
                if (!$contract) {
                    return redirect()->back()->with('error', 'Contract not found.');
                }
        
                $unitUsage = UsageUnit::all();
                $unitType = UnitType::all();
                $contract_periods = ContractPeriod::where('contract_type', $contract->contract_type)->get();
                $payment_types = PaymentType::where('contract_type', $contract->contract_type)->get();
     
                return view('website.contractReal.step6', compact('unitType','real' ,'unit','contract_periods', 'payment_types', 'unitUsage', 'contract'));
            // } catch (\Exception $e) {
            //             return redirect()->back()->with('error', 'حدث خطأ ما حاول مره اخري');
            // }
        }
     

        
        public function submitStep6(Request $request, $uuid, $real_id, $id)
        {
            try {
                $validatedData = $request->validate([
                    'contract_starting_date' => 'required|date',
                    'annual_rent_amount_for_the_unit' => 'required|numeric',
                    'contract_term_in_years' => 'required|exists:contract_periods,id',
                    'payment_type_id' => 'required|exists:payment_types,id',
                    'additional_conditions' => 'nullable|in:yes,no',
                    'other_conditions' => 'nullable|string',
                    'terms_condition' => 'required|accepted',
                ]);
        
                $data = [
                    'contract_starting_date' => date('Y-m-d', strtotime($request->contract_starting_date)),
                    'annual_rent_amount_for_the_unit' => $request->annual_rent_amount_for_the_unit,
                    'contract_term_in_years' => $request->contract_term_in_years,
                    'payment_type_id' => $request->payment_type_id,
                    'additional_conditions' => $request->additional_conditions,
                    'other_conditions' => $request->other_conditions,
                ];
        
                $user_id = Auth::user()->id;
                $contract = Contract::where('uuid', $uuid)->where('user_id', $user_id)->first();
                $real=Contract::where('real_id',$real_id)->first();
                $unit = Contract::where('real_units_id',$contract->real_units_id)->first();
    
               
                if (!$contract) {
                    return redirect()->back()->with('error', 'Contract not found.');
                }
        
                $contract->update($data);
                $contract->update(['step' => 7]);
        
                return redirect()->route('Financial', ['uuid' => $contract->uuid]);
            } catch (\Exception $e) {
               return redirect()->back()->with('error', 'حدث خطأ ما حاول مره اخري');
            }
        }
        public function Financial($uuid, $real_id, $id)
        {
      
             try {
            $unit = UnitsReal::findOrFail($id);
            $real = RealEstate::with('contracts')->findOrFail($real_id);
            $user = Auth::user();
            $user_id = $user->id;
    
            $contract = Contract::where('uuid', $uuid)
                ->where('user_id', $user_id)
                ->firstOrFail();
    
                $contractPeriods = ContractPeriod::findOrFail($contract->contract_term_in_years);
                $accountsHandwrite = Account::first();
                $bankAccounts = BankAccount::all();
                $contract_type = $contract->contract_type;
        
               
        
                // Get active coupon
                $couponActive = Coupon::where('is_delete', 0)
                    ->where('date_start', '<=', now())
                    ->where('date_end', '>=', now())
                    ->first();
        
                $couponUsage = CouponUsage::where('contract_uuid', $contract->uuid)->first();
                $appliedCoupon = $couponUsage ? $couponUsage->coupon : null;
        
                 $totalContractPrice = $contract->getPriceContractAttribute();
        
                 $discountedPrice = 0;
                if ($appliedCoupon) {
                    if ($appliedCoupon->type_coupon === 'ratio') {
                        $discountedPrice = $totalContractPrice * ($appliedCoupon->value_coupon / 100);
                    } elseif ($appliedCoupon->type_coupon === 'value') {
                        $discountedPrice = $appliedCoupon->value_coupon;
                    }
                }
          
                $totalPriceDetails = $contract->getTotalPriceAttribute();
                $Pricing = ServicesPricing::where('contract_type', $contract_type)->get();
                $totalPricing = $Pricing->sum('price');
                $totalPriceDetails['total_price'] = $totalContractPrice + $contractPeriods->price + $totalPricing;

                $discountedPrice = $discountedPrice ?? 0;
                $totalPrice = isset($totalPriceDetails['total_price']) ? (float)$totalPriceDetails['total_price'] : 0;
                $discountedPrice = $discountedPrice ?? 0;
                $priceAfterCoupon = max(0, $totalPrice - $discountedPrice);
        
            return view('website.Contract.financial_statements', compact('contract','contract_coupon','couponUsage', 'couponActive', 'accountsHandwrite', 'unit', 'real', 'BankAccount', 'Pricing', 'contractPeriods', 'totalContractPrice'));
    
            }catch (\Exception $e){
                 return redirect()->back();
            }
    
        }
    
    
    
        public function getCities(Request $request)
        {
            $regionId = $request->input('regionId');
            $cities = City::where('region_id', $regionId)->get();
    
            return response()->json(['cities' => $cities]);
        }
}
