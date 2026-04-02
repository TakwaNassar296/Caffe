<?php

namespace App\Http\Controllers\Website;
use App\Http\Controllers\Controller;
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
use App\Models\Region;
use App\Models\ServicesPricing;
use App\Models\Setting;
use App\Models\UnitType;
use App\Models\UsageUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use TaqnyatSms;

class ContractController extends Controller{

    public function ContractNew(Request $request)
    {
         $validatedData = $request->validate([
            'contract_type' => 'required|in:housing,commercial',
        ],
      
        [
            'contract_type.required' => 'اختر نوع العقد',
            'contract_type.in' => 'النوع المختار غير صالح',
        ]);
    
        $selectedContractType = $request->input('contract_type');
        $contractTypeValue = $selectedContractType;
        $contract = new Contract();
        $contract->user_id = Auth::id();
        $contract->contract_type = $contractTypeValue;
        $contract->step = 1;
        $contract->save();
        $uuid = $contract->uuid;
        return redirect()->route('pricing', ['uuid' => $uuid]);
    }
    

    public function pricing($uuid)
    {
        $user_id = Auth::id();
        $contract = Contract::where('uuid', $uuid)->where('user_id', $user_id)->firstOrFail();
        
        $pricing = $contract->services;
        $step = $contract->step;
        $setting = Setting::first();
        $contractPeriod = ContractPeriod::where('contract_type', $contract->contract_type)->get();
    
        return view('website.Contract.pricing', compact('pricing', 'contract', 'setting', 'contractPeriod'));
    }

     
     public function paperwork($uuid)
     {
        try {
             $user_id = Auth::user()->id;
             $contract = Contract::where('uuid', $uuid)->where('user_id', $user_id)->first();
             $paperWorks=Paperwork::where('contract_type', $contract->contract_type)->get();
             $setting=Setting::first();

             if (!$contract) {
                 return redirect()->back()->with('error', 'Contract not found or unauthorized access.');
             }
     
             $step = $contract->step;
     
             if (!($step >6))
             {
                 return view('website.Contract.work_paper', compact('contract','paperWorks','setting'));
             } else {
                 return redirect()->intended('/financial_statements/' . $contract->uuid)
                                  ->with('error', 'لا يمكن الرجوع الي هذه الخطوة');
             }
         } catch (\Exception $e) {
             return redirect()->back()->with('error', 'هناك خطأ ما حاول في مره أخري.');
         }
     }
     

     public function Step1($uuid)
     {
         try
         {
             $setting=Setting::first();
             $value_contract=Account::first();
             $user_id = Auth::user()->id;
             $contract = Contract::where('uuid', $uuid)
                                 ->where('user_id', $user_id)
                                 ->first();
            $step = $contract->step;                 
            $regions = Region::all();
            $realTypes = ReaEstatType::where('contract_type', $contract->contract_type)->get();
            $usages = ReaEstatUsage::where('contract_type', $contract->contract_type)->get();
            return view('website.Contract.step1', compact('realTypes','value_contract','regions' ,'usages', 'contract','setting'));
         }
         catch (\Exception $e) {
             return redirect()->back()->with('error', 'هناك خطأ ما حاول في مره أخري.');
         }
     }
     
    

      public function submitStep1(Request $request, $uuid)
      {
         
         $validatedData = $request->validate([
            'contract_ownership' => 'required', 'in:owner,tenant',
            'property_owner_is_deceased' => 'required|in:1', 
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
             $user_id = Auth::user()->id;
             $contract = Contract::where('uuid', $uuid)->where('user_id', $user_id)->firstOrFail();
    
             $data = [
                'contract_ownership' => $request->contract_ownership,
                'property_owner_is_deceased' => $request->property_owner_is_deceased,
                'instrument_type' => $request->instrument_type,
                'property_type_id' => $request->property_type_id,
                'property_usages_id' => $request->property_usages_id,
                'property_city_id' => $request->property_city_id,
                'property_place_id' => $request->property_place_id,
                'neighborhood' => $request->neighborhood,
                'building_number' => $request->building_number,
                'postal_code' => $request->postal_code,
                'instrument_number'=>$request->instrument_number,
                'instrument_type'=>$request->instrument_type,
                'real_estate_registry_number'=>$request->real_estate_registry_number,
                'instrument_history'=>$request->instrument_history,
                'date_first_registration'=>$request->date_first_registration,
                'extra_figure' => $request->extra_figure,
                'unit_number_of_real'=>$request->unit_number_of_real,
                'number_of_floors' => $request->number_of_floors,
                'street' => $request->street,
                'step' => 2,  
            ];
     
            $contract->update($data);
            // dd($data);
            return redirect()->route('step2', $contract->uuid);    
       }  
        catch (\Exception $e) {
        return redirect()->back()->with('error', 'هناك خطأ ما حاول في مره أخري.');
    }
}

    public function PageStep2($uuid)
    {
        try {
            $user_id=Auth::user()->id;
            $contract=Contract::where('uuid',$uuid )->where('user_id',$user_id)->first();
            $regions=Region::all();

            $step = $contract->step;

            return view ('website.Contract.step2',compact('regions','contract'));
          
         }
         catch (\Exception $e){
          return redirect()->back()->with('error', 'هناك خطأ ما حاول في مره أخري.');
    }

    }
    public function submitStep2(Request $request, $uuid)
   {
    
    $rules = [
        'property_place_id' => 'required|integer|exists:regions,id', 
        'property_city_id' => 'required|integer|exists:cities,id',  
        'neighborhood' => 'required|string|max:255',
        'street' => 'required|string|max:255',
        'building_number' => 'required|string|max:50',
        'postal_code' => 'required|string|min:5|max:5',
        'extra_figure' => 'nullable|string|min:4|max:4',  
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

    // Validate the request with custom messages
    $request->validate($rules, $messages);

    try {
        $user_id = Auth::user()->id;
        $contract = Contract::where('uuid', $uuid)->where('user_id', $user_id)->first();

        if (!$contract) {
            return redirect()->back()->withErrors(['error' => 'العقد غير موجود أو ليس لديك صلاحية الوصول إليه.']);
        }

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

        return redirect()->route('contract.step3', $contract->uuid);
    } catch (\Exception $e) {
        return redirect()->back()->withErrors(['error' => 'حدث خطأ ما، يرجى المحاولة مرة أخرى.']);
    }
}

    
    
    public function contract_step3($uuid)
{
    try {
        $user_id = Auth::user()->id;
        $contract = Contract::where('uuid', $uuid)->where('user_id', $user_id)->first();
        if (!$contract) {
            return redirect()->back()->withErrors(['error' => 'حدثت مشكله ما اثناء العمل.']);
        }
        $setting=Setting::first();
        $unitUsage = UsageUnit::where('contract_type', $contract->contract_type)->get();
        $units = UnitType::where('contract_type', $contract->contract_type)->get();
        $step = $contract->step;
        return view('website.Contract.step3', compact('units','setting', 'contract', 'unitUsage'));
        
    } catch (\Exception $e) {
        return redirect()->back()->withErrors(['error' => 'حدث خطأ ما برجاء المحاوله مره اخري ']);
    }
}


public function submitStep3(Request $request, $uuid)
{
    $user_id = Auth::user()->id;

     $rules = [
        'name_owner' => 'required|string',
        'property_owner_id_num' => 'required|min:10',
        'property_owner_dob_hijri' => 'required', 
        'property_owner_mobile' => 'required|min:10|regex:/^05[0-9]{8}$/',
        'property_owner_iban' => 'nullable|min:22',
        'add_legal_agent_of_owner'=>'required',
        'id_num_of_property_owner_agent' => 'nullable|required_if:add_legal_agent_of_owner,1|min:10',
        'dob_hijri_of_property_owner_agent' => 'nullable|required_if:add_legal_agent_of_owner,1', 
        'mobile_of_property_owner_agent' => 'nullable|required_if:add_legal_agent_of_owner,1|min:10|regex:/^05[0-9]{8}$/',
        'agency_number_in_instrument_of_property_owner' => 'nullable|required_if:add_legal_agent_of_owner,1',
        'agency_instrument_date_of_property_owner' => 'nullable|required_if:add_legal_agent_of_owner,1', 
    ];

    // Define custom validation messages in Arabic
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

    // Validate the request data
    $validator = Validator::make($request->all(), $rules, $messages);

     $validator->after(function ($validator) use ($request) {
        $dob = $request->input('property_owner_dob_hijri');

         $dateParts = explode('-', $dob);
        if (count($dateParts) === 3) {
            $birthDate = \Carbon\Carbon::createFromFormat('d-m-Y', $dob);
            $age = now()->diffInYears($birthDate);

            if ($age < 18) {
                $validator->errors()->add('property_owner_dob_hijri', 'يجب أن يكون عمرك 18 عامًا على الأقل.');
            }
        }
    });

    if ($validator->fails()) {
        return redirect()->back()->withErrors($validator)->withInput();
    }

    try {
        $contract = Contract::where('uuid', $uuid)->where('user_id', $user_id)->firstOrFail();
        
        $data = [
            'step' => 4,
            'name_owner' => $request->input('name_owner'),
            'property_owner_id_num' => $request->input('property_owner_id_num'),
            'property_owner_dob_hijri' => $request->input('property_owner_dob_hijri'),
            'property_owner_mobile' => $request->input('property_owner_mobile'),
            'add_legal_agent_of_owner' => $request->input('add_legal_agent_of_owner'),
            'property_owner_iban' => $request->input('property_owner_iban'),
            'agent_iban_of_property_owner' => $request->input('agent_iban_of_property_owner') ?? null,
            'id_num_of_property_owner_agent' => $request->input('id_num_of_property_owner_agent') ?? null,
            'dob_hijri_of_property_owner_agent' => $request->input('dob_hijri_of_property_owner_agent') ?? null,
            'mobile_of_property_owner_agent' => $request->input('mobile_of_property_owner_agent') ?? null,
            'agency_number_in_instrument_of_property_owner' => $request->input('agency_number_in_instrument_of_property_owner') ?? null,
            'agency_instrument_date_of_property_owner' => $request->input('agency_instrument_date_of_property_owner') ?? null,
        ];

        
        $contract->update($data);

        return redirect()->route('contract.step4', $contract->uuid);
    } catch (\Exception $e) {
         return redirect()->back()->withErrors(['error' => 'حدث خطأ ما، يرجى المحاولة مرة أخرى.']);
    }
}


    public function contract_step4($uuid){
        try{
            $user_id=Auth::user()->id;
            $regions=Region::all();
            $setting=Setting::first();

            $contract=Contract::where('uuid',$uuid )->where('user_id',$user_id)->first();
            $payment_types=PaymentType::Where('contract_type',$contract->contract_type)->get();
            $step = $contract->step;   
            return view('website.Contract.step4',compact('regions','setting','contract'));
        
        }catch (\Exception $e){
            return redirect()->back();
        }

    }
  
    public function submitStep4(Request $request, $uuid)
{
    try {
        $user_id = Auth::id();

        $contract = Contract::where('uuid', $uuid)->where('user_id', $user_id)->first();

        if (!$contract) {
            return redirect()->back()->withErrors(['msg' => 'العقد غير موجود.']);
        }

        $validatedData = $request->validate([
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
        return redirect()->route('contract.step5', $contract->uuid);
    
        } catch (\Exception $e) {

            return redirect()->back()->withErrors(['msg' => 'حدث خطأ يرجي المحاوله في وقت لاحق: ' ]);
        }
    }
    


    public function contract_step5($uuid)
    {
        
            $user_id = Auth::user()->id;
            $contract = Contract::where('uuid', $uuid)->where('user_id', $user_id)->first();
    
            if (!$contract) {
                return redirect()->back()->with('error', 'Contract not found.');
            }
    
            $unitUsage = UsageUnit::where('contract_type',$contract->contract_type)->get();
            $unitType = UnitType::where('contract_type',$contract->contract_type)->get();
            $step = $contract->step;
    
           return view('website.Contract.step5', compact('unitType', 'unitUsage', 'contract'));
       
    }

    
    public function submitStep5(Request $request, $uuid)
    {
        //  try {
        $validatedData = $request->validate([
            'unit_type_id' => 'required|exists:unit_types,id',
            'unit_usage_id' => 'required|exists:unit_usage,id',
            'unit_number' => 'required|string|max:255',
            'floor_number' => 'required|integer|max:10',
            'unit_area' => 'required|numeric',
            'tootal_rooms' => 'nullable|integer|max:10',  
            'The_number_of_halls' => 'nullable|integer|max:10',  
            'The_number_of_kitchens' => 'nullable|integer|max:10',  
            'The_number_of_toilets' => 'nullable|integer|max:10', 
            'window_ac' => 'nullable|integer|max:10',
            'split_ac' => 'nullable|integer|max:10',
            'electricity_meter_number' => 'nullable|string|max:255',
            'water_meter_number' => 'nullable|string|max:255',
        ]);
    
        $user_id = Auth::user()->id;
        $contract = Contract::where('uuid', $uuid)->where('user_id', $user_id)->first();
    
        if (!$contract) {
            return redirect()->back()->with('error', 'Contract not found.');
        }
    
         $contract->update([
            'unit_type_id' => $validatedData['unit_type_id'],
            'unit_usage_id' => $validatedData['unit_usage_id'],
            'unit_number' => $validatedData['unit_number'],
            'floor_number' => $validatedData['floor_number'],
            'unit_area' => $validatedData['unit_area'],
            'tootal_rooms' => $validatedData['tootal_rooms'] ?? null,
            'The_number_of_halls' => $validatedData['The_number_of_halls']?? null,
            'The_number_of_kitchens' => $validatedData['The_number_of_kitchens']?? null,
            'The_number_of_toilets' => $validatedData['The_number_of_toilets']?? null,
            'window_ac' => $validatedData['window_ac']?? null,
            'split_ac' => $validatedData['split_ac']?? null,
            'electricity_meter_number' => $validatedData['electricity_meter_number']?? null,
            'water_meter_number' => $validatedData['water_meter_number']?? null,
        ]);
    
         $contract->update(['step' => 6]);
    
        return redirect()->route('contract.step6', ['uuid' => $contract->uuid]);
        
    // } catch (\Exception $e) {

    //         return redirect()->back()->withErrors(['msg' => 'حدث خطأ يرجي المحاوله في وقت لاحق: ' ]);
    //     }
    }
    

    public function contract_step6($uuid)
    {
        try {
            $user_id = Auth::user()->id;
            $contract = Contract::where('uuid', $uuid)->where('user_id', $user_id)->first();
    
            if (!$contract) {
                return redirect()->back()->with('error', 'Contract not found.');
            }
    
            $unitUsage = UsageUnit::all();
            $unitType = UnitType::all();
            $contract_periods = ContractPeriod::where('contract_type', $contract->contract_type)->get();
            $payment_types = PaymentType::where('contract_type', $contract->contract_type)->get();
    
            return view('website.Contract.step6', compact('unitType', 'contract_periods', 'payment_types', 'unitUsage', 'contract'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
    

    public function submitStep6(Request $request, $uuid)
    {
          try {
            $validatedData = $request->validate([
                'contract_starting_date' => 'required',
                'annual_rent_amount_for_the_unit' => 'required',
                'contract_term_in_years' => 'required|exists:contract_periods,id',
                'payment_type_id' => 'exists:payment_types,id',
            
                'other_conditions' => 'nullable|string',
             ]);
    
            $data = [
                'contract_starting_date' => $request->contract_starting_date,
                'annual_rent_amount_for_the_unit' => $request->annual_rent_amount_for_the_unit,
                'contract_term_in_years' => $request->contract_term_in_years,
                'payment_type_id' => $request->payment_type_id,
                'other_conditions' => $request->other_conditions,
            ];
     
            $user_id = Auth::user()->id;
            $contract = Contract::where('uuid', $uuid)->where('user_id', $user_id)->first();
    
            if (!$contract) {
                return redirect()->back()->with('error', 'Contract not found.');
            }
    
            $contract->update($data);
            $contract->update(['step' => 7]);
    
            return redirect()->route('Financial', ['uuid' => $contract->uuid]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
    

    public function getCities(Request $request)
    {
        $regionId = $request->input('regionId');
        $cities = City::where('region_id', $regionId)->get();
        return response()->json(['cities' => $cities]);
    }

    public function Financial($uuid){
        //  try {
            $user = Auth::user();
            $user_id = $user->id;
    
            // Find the contract
            $contract = Contract::where('uuid', $uuid)
                ->where('user_id', $user_id)
                ->firstOrFail();
    
     
            
         $contractPeriod = ContractPeriod::where('contract_type', $contract->contract_type)->where('id', $contract->contract_term_in_years)->firstOrFail();
        
            $accountsHandwrite = Account::first();
            $bankAccounts = BankAccount::all();
            $contract_type = $contract->contract_type;
    
            $settings=Setting::first();

            // Get active coupon
            $couponActive = Coupon::where('is_delete', 0)
                ->where('date_start', '<=', now())
                ->where('date_end', '>=', now())
                ->first();
    
            $contractCouponUsage = CouponUsage::where('contract_uuid', $contract->uuid)->first();
            $appliedCoupon = $contractCouponUsage ? $contractCouponUsage->coupon : null;
    
             $totalContractPrice = $contract->getPriceContractAttribute();
    
             $discountedPrice = 0;
            if ($appliedCoupon) {
                if ($appliedCoupon->type_coupon === 'ratio') {
                    $discountedPrice = $totalContractPrice * ($appliedCoupon->value_coupon / 100);
                } elseif ($appliedCoupon->type_coupon === 'value') {
                    $discountedPrice = $appliedCoupon->value_coupon;
                }
            }
            $Pricing = ServicesPricing::where('contract_type', $contract_type)->get();
            $totalPricing = $Pricing->sum('price');
            $app_price = ($settings->housing_tax ?? 0) + ($settings->commercial_tax ?? 0) + ($settings->application_fees ?? 0)  ;

            $totalPriceDetails = $contract->getTotalPriceAttribute();
            $totalPriceDetails['total_price'] = $totalContractPrice + ($contractPeriod->price)+$totalPricing + $app_price ;  
            $discountedPrice = $discountedPrice ?? 0;
            $totalPrice = (isset($totalPriceDetails['total_price']) ? (float)$totalPriceDetails['total_price'] : 0) ;
            $discountedPrice = $discountedPrice ?? 0;
            $priceAfterCoupon = max(0, $totalPrice - $discountedPrice);

            return view('website.Contract.financial_statements', compact(
                'contract',
                'couponActive',
                'accountsHandwrite',
                'bankAccounts',
                'contractCouponUsage',
                'settings',
                'Pricing',
                'contractPeriod',
                'totalPriceDetails',
                'priceAfterCoupon',
                'discountedPrice'
            ));
        // } catch (\Exception $e) {

        //      return redirect()->back()->with('error', 'حدث خطأ ما يرجي المحاوله مره أخري ');

        // }
    }
     
 
    
    }
    
    
    
     

   
  
   
 