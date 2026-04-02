<?php

namespace App\Http\Controllers\website;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\RealEstate;
use App\Models\UnitsReal;
use Illuminate\Http\Request;

class UnCompeleteController extends Controller
{
    //get uncompeleted  contract
    public function LastStep(Contract $MyContract)
    {

         $step = $MyContract->step;
          
        

         if($step==0 )
         {
           return redirect()->route('contract.create.real', [$MyContract->uuid,$MyContract->real_id,$MyContract->real_units_id]);
         }

        if ($step == 1) {
           return redirect()->route('real.step1', [$MyContract->uuid,$MyContract->real_id,$MyContract->real_units_id]);
        } else if ($step == 2) {
            return redirect()->route('real.step2', [$MyContract->uuid,$MyContract->real_id,$MyContract->real_units_id]);


        } else if ($step == 3) {
            return redirect()->route('real.contract.step3', [$MyContract->uuid,$MyContract->real_id,$MyContract->real_units_id]);
        } else if ($step == 4) {
            return redirect()->route('real.contract.step4', [$MyContract->uuid,$MyContract->real_id,$MyContract->real_units_id]);
        }else if ($step == 5) {
            return redirect()->route('Financial', [$MyContract->uuid,$MyContract->real_id,$MyContract->real_units_id]);
        }


    }

    public function LastContract()
    {
         $user = auth()->user();
    
         $contract = Contract::where('user_id', $user->id)
            ->where('is_completed', false)
            ->whereNotNull('real_id')
            ->whereNotNull('real_units_id')
            ->latest('created_at')
            ->first();
    
         if ($contract) {
             return redirect()->route('UnCompleted.real', [
                'MyContract' => $contract->id,
                'uuid' => $contract->uuid,
                'real_id' => $contract->real_id,
                'unit_id' => $contract->real_units_id
            ]);
        } else {
             return redirect()->back()->with('error', trans('website.not-found-contract'));
        }
    }

    public function CheckContract(Contract $MyContract)
    {
        if ($MyContract->real_id != null) {
            return redirect()->route('UnCompleted.real', [
                'MyContract' => $MyContract->id,
                'uuid' => $MyContract->uuid,
                'real_id' => $MyContract->real_id,
                'unit_id' => $MyContract->real_units_id
            ]);
        } else {
            return redirect()->route('UnCompleted', $MyContract);
        }
    }
    
}

