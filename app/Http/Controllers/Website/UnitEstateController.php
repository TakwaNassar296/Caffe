<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\RealEstate;
use App\Models\UnitsReal;
use App\Models\UnitType;
use App\Models\UsageUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use SebastianBergmann\CodeCoverage\Report\Xml\Unit;

class UnitEstateController extends Controller
{

     //  Unit Curds
     public function unit($id)
     {
          $userReal = RealEstate::findOrFail($id);
     
          $units = UnitsReal::where('real_estates_units_id', $userReal->id)
           ->paginate(3);
     
          $contract = Contract::where('real_id', $userReal->id)->first();  
     
         return view('website.units.index', compact('units', 'contract', 'userReal'));
     }
     
    public function createUnit($id)
    {
        $unitReal = RealEstate::findOrFail($id);
        $unitType = UnitType::where('contract_type',$unitReal->contract_type ??'housing')->get();
        $unitUsage = UsageUnit::where('contract_type',$unitReal->contract_type ??'housing')->get();
         return view('website.units.create', compact('unitReal','unitUsage','unitType'));
    }

     
    public function storeUnit(Request $request, $id)
    {
        try {
         $realEstate = RealEstate::findOrFail($id);
    
         $rules = [
            'unit_type_id' => 'required|exists:unit_types,id',
            'unit_usage_id' => 'required|exists:unit_usage,id',
            'unit_area' => 'required|numeric',
            
        ];
    

         $request->validate($rules);
    
         $data = $request->only([
            'unit_type_id', 'unit_usage_id', 'The_number_of_halls','unit_area', 'floor_number', 'sub_delay',
            'rooms_number', 'tootal_rooms','The_number_of_toilets', 'The_number_of_kitchens',
            'window_ac', 'split_ac', 'electricity_meter_number', 'water_meter_number', 'unit_number'
        ]);
        

        $data['real_estates_units_id'] = $realEstate->id;
        $data['user_id'] = Auth::id();
    
         UnitsReal::create($data);
    
         return redirect()->route('unit', ['id' => $realEstate->id])
                         ->with('success', 'تمت الأضافه بنجاح');

        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء الإضافة: ' . $e->getMessage());
        }
    }
    
    public function edit($id)
    {
        // Fetch the unit
        $unitReal = UnitsReal::findOrFail($id);
    
        // Fetch the related Real Estate using the real_estates_units_id
        $Real = RealEstate::findOrFail($unitReal->real_estates_units_id);
    
        // Fetch unit types and usage units based on the contract type of the real estate
        $unitType = UnitType::where('contract_type', $Real->contract_type)->get();
        $unitUsage = UsageUnit::where('contract_type', $Real->contract_type)->get();
    
        // Return the view with the fetched data
        return view('website.units.edit', compact('unitReal', 'Real', 'unitType', 'unitUsage'));
    }
    

    public function updateStepOne(Request $request, $id)
    {
        try {
        $unitReal = UnitsReal::findOrFail($id);
    
         $userReal = RealEstate::findOrFail($unitReal->real_estates_units_id);
    
         $units = UnitsReal::where('real_estates_units_id', $userReal->id)->get();
    
         $rules = [
            'water_meter_number' =>'nullable',
            'electricity_meter_number' =>'nullable',
            'Number_parking_spaces' =>'nullable',
            'Gasmeter' =>'nullable',
            'unit_area' =>'required',
            'split_ac'=>'sometimes',
            'window_ac'=>'sometimes',
            'sub_delay'=>'required',
            'The_number_of_halls' =>'nullable',
            'floor_number' =>'required',
        ];
    
        $this->validate($request, $rules);
    
         $data = $request->only([
            'tootal_rooms', 'The_number_of_the_toilet', 'real_estates_units',
            'The_number_of_halls','sub_delay' ,'The_number_of_kitchens', 'property_city_id', 
            'unit_area', 'number_of_unit_air_conditioners','split_ac','window_ac' ,'water_meter_number',
            'electricity_meter_number', 'Services', 'unit_number', 'unit_usage_id',
            'unit_type_id', 'floor_number', 'Gasmeter', 'real_estates_units_id', 'The_number_of_toilets',
            'Number_parking_spaces',
        ]);
    
         $data['user_id'] = Auth::id();
    
         $unitReal->update($data);
    
         return redirect()->route('unit', [$unitReal->real_estates_units_id])

         ->with('success', 'تمت التعديل بنجاح');
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء التعديل: ' . $e->getMessage());
        }
     }
    


    public function show($id)
    {
        try {
            $item  = UnitsReal::findOrFail($id);
            return view('website.units.show', compact('item'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
             abort(404);   
        }
    }
    
    public function destroy($id)
    {
         $unit = UnitsReal::findOrFail($id); 
    
         $realEstate = UnitsReal::where('real_estates_units_id', $unit->id)->first();
    
         $unit->delete();
    
         return redirect()->back()->with('success', 'تم الحذف بنجاح');
    }
    
 
}
 