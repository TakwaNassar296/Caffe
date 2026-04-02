<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\RealEstateRequest;
use App\Models\Account;
use App\Models\City;
use App\Models\Contract;
use App\Models\ReaEstatType;
use App\Models\ReaEstatUsage;
use App\Models\RealEstate;
use App\Models\Region;
use App\Models\UnitsReal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RealEstateController extends Controller
{
   public function index()
    {
        $user_id = Auth::user()->id;
        $real = RealEstate::where('user_id', $user_id)
        ->orderBy('created_at', 'desc')
        ->paginate(3);
            
        $units = UnitsReal::all(); 

         return view('website.RealEstate.index', compact('real', 'units'));
    }
    
    public function NewRealEstate(Request $request)
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
    
        $real = new RealEstate();
        $real->user_id = Auth::id();
        $real->contract_type = $contractTypeValue;
        $real->save();
         return redirect()->route('create.step1.realEstate', ['id' => $real->id]);
    }
    

    public function createStepOneReal($id)
    {
        $realEstate = RealEstate::findOrFail($id);
        $realTypes = ReaEstatType::where('contract_type',$realEstate->contract_type)->get();
        $usages = ReaEstatUsage::where('contract_type',$realEstate->contract_type)->get();
        $valueContract = Account::first()?->id ?? 55;
        $cities = City::all();
        $regions = Region::all();

     return view('website.RealEstate.step1', compact('valueContract', 'realEstate', 'realTypes', 'usages', 'cities', 'regions'));
    }
 
    public function storeStepOne(Request $request,$id){
 
     $validatedData = $request->validate([
        'contract_ownership' => 'required',  
        'property_owner_is_deceased' => 'required|in:1', 
        'instrument_type' => 'nullable|in:electronic,strong_argument',  
        'instrument_number' => 'nullable|string|max:255|required_if:instrument_type,electronic',  
        'instrument_history' => 'nullable|date|required_if:instrument_type,electronic',  
        'date_first_registration' => 'nullable|date|required_if:instrument_type,strong_argument',  
        'real_estate_registry_number' => 'nullable|string|required_if:instrument_type,strong_argument',
        'property_type_id' => 'nullable|exists:rea_estat_types,id', 
        'property_usages_id' => 'nullable|exists:rea_estat_usages,id', 
        'number_of_floors' => 'nullable|integer|min:1|max:10',  
        'unit_number_of_real' => 'nullable|integer|min:1|max:10',  
    ], 
    [
        'instrument_type.required' => 'اختر نوع الصك',
        'contract_ownership.required' => 'اختر صاحب العقار',
        'property_owner_is_deceased.required' => 'اختر حالة المتوفي',
        'instrument_type.in' => 'نوع الصك غير صالح',
        'instrument_number.string' => 'رقم الصك يجب أن يكون نصاً',
        'instrument_history.date' => 'تاريخ الصك غير صالح',
        'date_first_registration.date' => 'تاريخ التسجيل الأول غير صالح',
        'property_type_id.exists' => 'نوع العقار غير صالح',
        'property_usages_id.exists' => 'استخدام العقار غير صالح',
        'number_of_floors.integer' => 'عدد الطوابق يجب أن يكون رقماً',
        'unit_number_of_real.integer' => 'عدد الوحدات يجب أن يكون رقماً',
    ]);

    $selectedContractOwner = $request->input('contract_ownership');
    $selectedOwnerDeceased = $request->input('property_owner_is_deceased');
    $instrumentType = $request->input('instrument_type');
    $documentNumber = $request->input('instrument_number');
    $instrumentHistory = $request->input('instrument_history');
    $dateFirstRegistration = $request->input('date_first_registration');
    $propertyType = $request->input('property_type_id');
    $propertyUsage = $request->input('property_usages_id');
    $floorsNumber = $request->input('number_of_floors');
    $unitsNumber = $request->input('unit_number_of_real');
    $RealEstateRegistryNumber=$request->input('real_estate_registry_number');

    $realEstate = RealEstate::findOrFail($id);
     if (!$realEstate) {
        return redirect()->back()->with('error', 'عقار غير موجود');
    }

    $realEstate->contract_ownership = $selectedContractOwner;  
    $realEstate->property_owner_is_deceased = $selectedOwnerDeceased;  
    $realEstate->instrument_type = $instrumentType; 
    $realEstate->instrument_number = $documentNumber;
    $realEstate->instrument_history = $instrumentHistory; 
    $realEstate->date_first_registration = $dateFirstRegistration; 
    $realEstate->property_type_id = $propertyType; 
    $realEstate->real_estate_registry_number = $RealEstateRegistryNumber; 
    $realEstate->property_usages_id = $propertyUsage; 
    $realEstate->number_of_floors = $floorsNumber; 
    $realEstate->unit_number_of_real = $unitsNumber; 
    $realEstate->step = 1;
    $realEstate->save();  
 
    return redirect()->route('create.step2.realEstate', ['id' => $realEstate->id]);
}


    public function stepTwo($id)
    {
        $realEstate = RealEstate::findOrFail($id);
    
        if (!$realEstate) {
            return redirect()->back()->with('error', 'عقار غير موجود');
        }

        $contract=Contract::where('real_id',$realEstate->id)->first();
        
        $realTypes=ReaEstatType::where('contract_type', $realEstate->contract_type)->get();
        $usages=ReaEstatUsage::where('contract_type', $realEstate->contract_type)->get();
        
        $cities = City::all();
        $regions = Region::all();

            
        return view('website.RealEstate.step2', compact('realTypes', 'usages', 'realEstate', 'cities', 'regions'));
    }
    
    public function stepTwoStore(Request $request, $id)
    {
          $messages = [
            'property_place_id.required' => 'حقل مكان العقار مطلوب.',
            'property_place_id.exists' => 'مكان العقار المحدد غير موجود.',
            'property_city_id.required' => 'حقل المدينة للعقار مطلوب.',
            'property_city_id.exists' => 'المدينة المحددة للعقار غير موجودة.',
            'neighborhood.required' => 'حقل الحي مطلوب.',
            'neighborhood.string' => 'حقل الحي يجب أن يكون نصاً.',
            'neighborhood.max' => 'حقل الحي يجب ألا يتجاوز 255 حرفاً.',
            'street.required' => 'حقل الشارع مطلوب.',
            'street.string' => 'حقل الشارع يجب أن يكون نصاً.',
            'street.max' => 'حقل الشارع يجب ألا يتجاوز 255 حرفاً.',
            'building_number.required' => 'حقل رقم المبنى مطلوب.',
            'building_number.numeric' => 'حقل رقم المبنى يجب أن يكون رقماً.',
            'postal_code.required' => 'حقل الرمز البريدي مطلوب.',
            'postal_code.numeric' => 'حقل الرمز البريدي يجب أن يكون رقماً.',
            'extra_figure.required' => 'حقل الرقم الإضافي مطلوب.',
            'extra_figure.numeric' => 'حقل الرقم الإضافي يجب أن يكون رقماً.',
        ];
    
        $validatedData = $request->validate([
            'property_place_id' => 'required|exists:regions,id',
            'property_city_id' => 'required|exists:cities,id',
            'neighborhood' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'building_number' => 'required|numeric',
            'postal_code' => 'required|numeric',
            'extra_figure' => 'required|numeric',
        ], $messages);
    
        $realEstate = RealEstate::findOrFail($id);
    
        if (!$realEstate) {
            return redirect()->back()->with('error', 'عقار غير موجود');
        }
    
        $realEstate->update($validatedData);
        $realEstate->step = 3;
        $realEstate->save();
    
        return redirect()->route('createStep3.realEstate', ['id' => $realEstate->id]);
    }
     

    public function stepThree($id)
    {

        $realEstate = RealEstate::findOrFail($id);
        $cities = City::all();
        $regions = Region::all();
        return view('website.RealEstate.step3', compact('realEstate','cities','regions'));
     
    }

    public function stepThreeStore(Request $request, $id)
{
     // Validate the request data
    $validatedData = $request->validate([
        'name_owner' => 'required|string|max:255',
        'name_real_estate' => 'required|string|max:255',   
        'property_owner_id_num' => 'required|numeric|min:10',   
        'property_owner_dob_hijri' => 'required|string',  
        'property_owner_mobile' => 'required|string|regex:/^05[0-9]{8}$/',  
        'property_owner_iban' => 'nullable|string|min:22',  
        'add_legal_agent_of_owner' => 'required|string|in:1,0',
        'id_num_of_property_owner_agent' => 'nullable|string|digits:10',   
        'dob_of_property_owner_agent' => 'nullable|string',  
        'mobile_of_property_owner_agent' => 'nullable|required_if:add_legal_agent_of_owner,1|string|regex:/^05[0-9]{8}$/',  
        'agency_number_in_instrument_of_property_owner' => 'nullable|required_if:add_legal_agent_of_owner,1|string|max:255',
        'agency_instrument_date_of_property_owner' => 'nullable|required_if:add_legal_agent_of_owner,1|string',  
    ], [
        // Custom validation messages in Arabic
        'name_owner.required' => 'حقل اسم المالك مطلوب.',
        'name_owner.string' => 'حقل اسم المالك يجب أن يكون نصًا.',
        'name_owner.max' => 'حقل اسم المالك يجب ألا يزيد عن 255 حرفًا.',
        'name_real_estate.required' => 'حقل اسم العقار مطلوب.',
        'name_real_estate.string' => 'حقل اسم العقار يجب أن يكون نصًا.',
        'name_real_estate.max' => 'حقل اسم العقار يجب ألا يزيد عن 255 حرفًا.',
        'national_num.required' => 'حقل رقم الهوية الوطنية مطلوب.',
        'national_num.numeric' => 'حقل رقم الهوية الوطنية يجب أن يكون رقميًا.',
        'national_num.digits' => 'حقل رقم الهوية الوطنية يجب أن يكون 10 أرقام.',
        'dob_hijri.required' => 'حقل تاريخ الميلاد الهجري مطلوب.',
        'dob_hijri.string' => 'حقل تاريخ الميلاد الهجري يجب أن يكون نصًا.',
        'property_owner_id_num.min'=>'رقم الهويه يجب الا يقل عن عشرة ارقام',

        'property_owner_mobile.required' => 'حقل رقم الجوال مطلوب.',
        'property_owner_mobile.string' => 'حقل رقم الجوال يجب أن يكون نصًا.',
        'property_owner_mobile.regex' => 'حقل رقم الجوال يجب أن يبدأ بـ 05 ويتبعه 8 أرقام.',
        'property_owner_iban.required' => 'حقل رقم الآيبان مطلوب.',
        'property_owner_iban.string' => 'حقل رقم الآيبان يجب أن يكون نصًا.',
        'property_owner_iban.size' => 'حقل رقم الآيبان يجب أن يكون مكون من 22 حرفًا.',
        'property_owner_iban.regex' => 'حقل رقم الآيبان يجب أن يبدأ بـ SA ويتبعه 20 رقمًا.',
        'add_legal_agent_of_owner.required' => 'يجب تحديد ما إذا كان هناك وكيل قانوني للمالك أم لا.',
        'add_legal_agent_of_owner.in' => 'القيمة المدخلة في حقل وكيل المالك يجب أن تكون "1" أو "0".',
        'id_num_of_property_owner_agent.digits' => 'حقل رقم هوية وكيل المالك يجب أن يكون 10 أرقام.',
        'mobile_of_property_owner_agent.regex' => 'حقل رقم الجوال لوكيل المالك يجب أن يبدأ بـ 05 ويتبعه 8 أرقام.',
        'agency_number_in_instrument_of_property_owner.max' => 'حقل رقم الوكالة في صك المالك يجب ألا يزيد عن 255 حرفًا.',
        'agency_instrument_date_of_property_owner.required_if' => 'تاريخ صك الوكالة مطلوب عند إضافة وكيل قانوني.',
        'mobile_of_property_owner_agent.required_if' => 'رقم الجوال للوكيل العقاري مطلوب عندما تكون حالة الوكيل القانوني 1.',
        'mobile_of_property_owner_agent.regex' => 'رقم الجوال للوكيل العقاري يجب أن يبدأ بـ 05 ويتكون من 10 أرقام.',
        
        'agency_number_in_instrument_of_property_owner.required_if' => 'رقم الوكالة في وثيقة المالك العقاري مطلوب عندما تكون حالة الوكيل القانوني موجود.',
        'agency_number_in_instrument_of_property_owner.max' => 'رقم الوكالة في وثيقة المالك العقاري لا يمكن أن يتجاوز 255 حرفًا.',
        
        'agency_instrument_date_of_property_owner.required_if' => 'تاريخ وثيقة الوكالة للمالك العقاري مطلوب عندما  يكون الوكيل القانوني موجود.',
    
    ]);

    // Find the real estate record by ID
    $realEstate = RealEstate::find($id);

    if (!$realEstate) {
        return redirect()->back()->with('error', 'عقار غير موجود');
    }

    // Update the real estate with validated data
    $realEstate->update($validatedData);

    // Update the step in the real estate record
    $realEstate->step = 3;
    $realEstate->save();

    // Redirect to the next step with success message
    return redirect()->route('endForm.realEstate', ['id' => $realEstate->id])
                     ->with('success', 'تمت الأضافه بنجاح');
}

     
    public function endForm($id)
    {
         $realEstate = RealEstate::find($id);
         return view('website.RealEstate.end_form', compact('realEstate'));   
    }
    
    public function show($id)
    {
        
         $realEstate = RealEstate::find($id);
        return view('website.RealEstate.show', compact('realEstate'));
    }
    
    /*
    Edit function steps in realEstate 
    */

    public function editStepOne($id)
    {
        try {
             $real = RealEstate::findOrFail($id);
             $usages = ReaEstatUsage::where('contract_type', $real->contract_type)->get();
             $realTypes = ReaEstatType::where('contract_type', $real->contract_type)->get();
             $city = City::all();
             $regions = Region::all();
             $realEstate = RealEstate::findOrFail($id);    
            return view('website.RealEstate.step1_edit', compact('real', 'usages', 'realTypes', 'city', 'regions', 'realEstate'));
        } catch (\Exception $e) {
             \Log::error('Error in editStepOne: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'حدث خطأ أثناء معالجة الطلب. حاول مرة أخرى لاحقًا.']);
        }
    }
    

    
    
    public function updateStepOne(Request $request, $id)
    {
        // Validate the request
        $request->validate([
            // 'contract_ownership' => 'required',  
             
            'instrument_type' => 'nullable|in:electronic,strong_argument',
            'instrument_number' => 'nullable|string|max:255|required_if:instrument_type,electronic',  
            'instrument_history' => 'nullable|date|required_if:instrument_type,electronic',  
            'date_first_registration' => 'nullable|date|required_if:instrument_type,strong_argument',  
            'real_estate_registry_number' => 'nullable|string|required_if:instrument_type,strong_argument',
            
             'property_type_id' => 'required_if:instrument_type,electronic|required_if:instrument_type,strong_argument',
            'property_usages_id' => 'required_if:instrument_type,electronic|required_if:instrument_type,strong_argument',
            'number_of_floors' => 'nullable|integer|required_if:instrument_type,electronic|required_if:instrument_type,strong_argument',  
            'unit_number_of_real' => 'nullable|integer|required_if:instrument_type,electronic|required_if:instrument_type,strong_argument',  
        ]);
    
        // Find the real estate by ID
        $realEstate = RealEstate::findOrFail($id);
    
        // Update the real estate record
        $realEstate->update([
              'instrument_type' => $request->input('instrument_type'),
            'instrument_number' => $request->input('instrument_number'),
            'instrument_history' => $request->input('instrument_history'),
            'date_first_registration' => $request->input('date_first_registration'),
            'real_estate_registry_number' => $request->input('real_estate_registry_number'),
            'property_type_id' => $request->input('property_type_id'),
            'property_usages_id' => $request->input('property_usages_id'),
            'number_of_floors' => $request->input('number_of_floors'),
            'unit_number_of_real' => $request->input('unit_number_of_real'),
        ]);
    
        // Redirect to the show route with a success message
        return redirect()->route('realestate.show', [$id])->with('success', 'بيانات العقار تم تحديثها بنجاح');
    }
    
    public function editStepThree($id)
    {
        try{
        $realEstate = RealEstate::findOrFail($id);
        $cities = City::all();
        $realTypes = ReaEstatType::where('contract_type', $realEstate->contract_type)->get();
        $regions = Region::all();
        $usages=ReaEstatUsage::where('contract_type', $realEstate->contract_type)->get();

        return view('website.RealEstate.step3_edit', compact('cities','realEstate','usages','realTypes','regions'));
    } catch (\Exception $e) {
        \Log::error('Error in editStepOne: ' . $e->getMessage());
       return redirect()->back()->withErrors(['error' => 'حدث خطأ أثناء معالجة الطلب. حاول مرة أخرى لاحقًا.']);
   }
    }
    public function updateStepThree(Request $request, $id)
    {
        // Validate the request data
        $validated = $request->validate([
            'property_place_id' => 'required|exists:regions,id',
            'property_city_id' => 'required|exists:cities,id',
            'neighborhood' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'building_number' => 'required|numeric',
            'postal_code' => 'required|numeric',
            'extra_figure' => 'nullable|numeric',
        ], [
            'property_place_id.required' => 'حقل مكان العقار مطلوب.',
            'property_place_id.exists' => 'المكان المحدد غير موجود في قاعدة البيانات.',
            'property_city_id.required' => 'حقل المدينة مطلوب.',
            'property_city_id.exists' => 'المدينة المحددة غير موجودة في قاعدة البيانات.',
            'neighborhood.required' => 'حقل الحي مطلوب.',
            'neighborhood.string' => 'اسم الحي يجب أن يكون نصاً.',
            'neighborhood.max' => 'اسم الحي لا يمكن أن يتجاوز 255 حرفاً.',
            'street.required' => 'حقل الشارع مطلوب.',
            'street.string' => 'اسم الشارع يجب أن يكون نصاً.',
            'street.max' => 'اسم الشارع لا يمكن أن يتجاوز 255 حرفاً.',
            'building_number.required' => 'رقم المبنى مطلوب.',
            'building_number.numeric' => 'رقم المبنى يجب أن يكون رقماً.',
            'postal_code.required' => 'رمز البريد مطلوب.',
            'postal_code.numeric' => 'رمز البريد يجب أن يكون رقماً.',
            'extra_figure.numeric' => 'الرقم الإضافي يجب أن يكون رقماً.',
        ]);
    
        // Fetch the real estate record by ID, or fail if it doesn't exist
        $realEstate = RealEstate::findOrFail($id);
    
        // Update the real estate record with validated data
        $realEstate->update([
            'property_place_id' => $validated['property_place_id'],
            'property_city_id' => $validated['property_city_id'],
            'neighborhood' => $validated['neighborhood'],
            'street' => $validated['street'],
            'building_number' => $validated['building_number'],
            'postal_code' => $validated['postal_code'],
            'extra_figure' => $validated['extra_figure'] ?? null, // Handle null for nullable fields
        ]);
    
        // Redirect or return a response with a success message
        return redirect()->route('realestate.show', $realEstate->id)
            ->with('success', 'تم التحديث بنجاح.');
    }
    


    public function editStepTwo($id)
    {
        try{
         $realEstate = RealEstate::findOrFail($id);
        

        return view('website.RealEstate.step2_edit', compact( 'realEstate' ));
    } catch (\Exception $e) {
        \Log::error('Error in editStepOne: ' . $e->getMessage());
       return redirect()->back()->withErrors(['error' => 'حدث خطأ أثناء معالجة الطلب. حاول مرة أخرى لاحقًا.']);
   }
    }
    public function updateStepTwo(Request $request, $id)
    {

         // Validate the request data
         $validated = $request->validate([
            'property_owner_iban' => 'nullable|max:255|min:22',
            'name_owner' => 'required|string|max:255',
            'national_num' => 'required',
            'dob_hijri' => 'required',
             'mobile' => 'required|regex:/^05[0-9]{8}$/',

        ], [
            'property_owner_iban.required' => 'حقل ايبان المالك مطلوب.',
            'name_owner.required' => 'اسم مالك العقار مطلوب.',
            'property_owner_iban.max' => 'ايبان يجب أن يكون مكونًا من 22 رقم.',
            'property_owner_iban.min' => 'ايبان يجب أن يكون مكونًا من 22 رقم.',
            'national_num.required' => 'رقم الهوية الوطنية مطلوب.',
            'dob_hijri.required' => 'تاريخ الميلاد الهجري مطلوب.',
            'mobile.nullable' => 'رقم الهاتف المحمول اختياري.',
            'mobile.regex'=>'رقم الهاتف يبدا ب 05 ويتبعه ثمانية أرقام',
        ]);
        
    
         
        $realEstate = RealEstate::findOrFail($id);
    
         
        $realEstate->update([
             'property_owner_iban' => $validated['property_owner_iban'],
            'name_owner' => $validated['name_owner'],
            'national_num' => $validated['national_num'],
            'dob_hijri' => $validated['dob_hijri'],
            'mobile' => $validated['mobile'],
         ]);
    
        // Redirect or return a response
        return redirect()->route('realestate.show', $realEstate->id)
            ->with('success', 'تم التحديث بنجاح.');
    }
    

    
    public function delete($id)
    {
        $realEstate = RealEstate::with('units')->findOrFail($id);
            if ($realEstate->units->isNotEmpty()) {
                foreach ($realEstate->units as $unit) {
                    $unit->delete();
                }
            }
            $realEstate->delete();
            return redirect()->route('realEstate')->with('success', 'تم الحذف بنجاح');
    }
    


    public function getCities(Request $request)
    {
        $regionId = $request->input('regionId');
        $cities = City::where('region_id', $regionId)->get();

        return response()->json(['cities' => $cities]);
    }
}
 