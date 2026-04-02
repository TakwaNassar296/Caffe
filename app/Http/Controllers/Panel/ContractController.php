<?php
namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\City;
use App\Models\Contract;
use App\Models\ContractPeriod;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\ReaEstatType;
use App\Models\ReaEstatUsage;
use App\Models\Region;
use App\Models\ServicesPricing;
use App\Models\Setting;
use App\Models\UnitType;
use Illuminate\Http\Request;



class ContractController extends Controller
{
    public function index()
    {
        try {
        $contract = new Contract();
        $ContractUsers  = $contract->getContractAttribute();
        return view('panel.pages.contract.index', compact('ContractUsers'));
    } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
            ]);
        }
        
    }
    
    public function show($uuid)
    {
         $contract = Contract::where('uuid', $uuid)->firstOrFail();
        
         
        $contractPeriods = ContractPeriod::where('contract_type', $contract->contract_type)
            ->where('id', $contract->contract_term_in_years)
            ->first();  
        
       
        $contractPeriodsPrice = $contractPeriods ? $contractPeriods->price : '';


        $accountsHandwrite = Account::first();
        $contract_type = $contract->contract_type;
        $pricing = ServicesPricing::where('contract_type', $contract_type)->get();
    
         $couponActive = Coupon::where('is_delete', 0)
            ->where('date_start', '<=', now())
            ->where('date_end', '>=', now())
            ->first();
    
         $couponUsage = CouponUsage::where('contract_uuid', $contract->uuid)->first();
     
        $couponPrice = $couponUsage 
        ? Coupon::where('id', $couponUsage->coupon_id)->value('value_coupon') 
        : null;

 
        $totalContractPrice = $contract->getPriceContractAttribute();
        $settings = Setting::first();
        
     
        $app_price = ($settings->housing_tax ?? 0) 
                   + ($settings->commercial_tax ?? 0) 
                   + ($settings->application_fees ?? 0);
        
        $totalContractPrice += ($contractPeriods->price ?? 0) + $app_price;

     
        return view('panel.pages.contract.show', compact(
            'contract',
            'couponUsage',
            'couponActive',
            'accountsHandwrite',
            'contractPeriods',
            'pricing',
            'totalContractPrice',
            'couponPrice',
         ));
    }
    

    // show image function
    public function showImage($id)
    {
        $contract = Contract::findOrFail($id);
        $imagePath = null;
        if (!$imagePath || !file_exists($imagePath)) {
            $imagePath = null;
        }

        return view('panel.pages.contract.imageContract', compact('contract','imagePath'));
    }
    public function edit($id, Request $request)
    {
        $unitType = UnitType::all();
        $propertyCity = City::all();
        $propertyRegion=Region::all();
        $propertyType = ReaEstatType::all();
        $contractEdit = Contract::findOrFail($id);
        $propertyUsages = ReaEstatUsage::all();

        return view('panel.pages.contract.edit', compact('contractEdit','propertyUsages', 'propertyType', 'propertyCity','propertyRegion', 'unitType'));
    }

    public function resetDelete()
    {
        $contract = new Contract();
        $ContractUsers = $contract->getContractDeleteAttribute();
        return view('panel.pages.contract.reset', compact('ContractUsers'));
    }

 

    public function confirmReset($id)
    {
        $contract = Contract::findOrFail($id);
        $contract->is_delete = 0;
        $contract->save();
        return redirect()
            ->route('admin.contract.index')
            ->with('success', 'تمت العملية بنجاح');
    }

    public function deleteContract($uuid)
    {
        $contract = Contract::where('uuid', $uuid)->firstOrFail();
        $contract->is_delete = 1;
        $contract->save();
    
        return redirect()
            ->back()
            ->with('success', 'تمت العملية بنجاح');
    }
    
    
    public function changeStatus(Request $request, $uuid)
    {
        $request->validate([
            'notes' => 'required|string|max:1000',  
            'status' => 'required|in:retrieved,cancel,completed',   
        ]);
    
        $contract = Contract::where('uuid', $uuid)->firstOrFail();
    
        $contract->notes = $request->notes;
        $contract->status = $request->status;
    
         if ($request->status === 'completed') {
            $contract->is_completed = true;
        }
    
        $contract->save();
    
        return redirect()
            ->back()
            ->with('success', 'تم تغيير حالة العقد بنجاح.');
    }

    
  
    



 
    
}