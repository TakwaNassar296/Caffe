<?php

namespace App\Http\Controllers\website;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileRequest;
 use App\Models\Paperwork;
use App\Models\Contract;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ContractPeriod;
use App\Models\Account;
use App\Models\BankAccount;
use App\Models\ServicesPricing;
use App\Models\Coupon;
use App\Models\CouponUsage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response; 
use App\Models\Page;



class GeneralController extends Controller
{
       public function myContract(){
           
        $user_id = Auth::user()->id;
        $user = User::findOrFail($user_id);
    
         $myContracts = $user->contracts()
            ->where('step', '=', '7')
            
            ->orderBy('created_at', 'desc')
            ->get();
    
      
        $files = [];
    
        foreach ($myContracts as $contract) {
            $filePath = getFilePath($contract->file);   
            $filePath = str_replace('public/', '', $filePath);   
            $files[] = [
                'file' => $filePath,
                'created_at' => $contract->created_at,
                'user' => $contract->user_id,
                'contract_uuid' => $contract->uuid,
            ];
        }
    
         return view('website.Contract.myContract', compact('myContracts', 'user', 'files'));
    }


    public function ContractFile($uuid)
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

        return view('website.Contract.myContract', compact('files', 'contracts'));
    }


    public function myContracttest(){
        $user=Auth::user();
    }

  

    public function profile()
    {
        $user_id = Auth::user()->id;
        $user = User::findOrFail($user_id);
        
         $mobile = $user->mobile;
        if (substr($mobile, 0, 5) == '00966') {
            $mobile = substr($mobile, 5);  
        }
    
        return view('website.pages.profile', compact('user', 'mobile'));
    }
    
    public function updateProfile(Request $request, $id)
    {
        $request->validate([
            'fname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'mobile' => 'required|numeric|digits:9|regex:/^5[0-9]{8}$/|unique:users,mobile,' . $id,
            'password' => 'nullable|min:8|confirmed',
        ], [
            'fname.required' => 'الاسم الأول مطلوب.',
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email' => 'يجب أن يكون البريد الإلكتروني عنوان بريد إلكتروني صالح.',
            'email.unique' => 'هذا البريد الإلكتروني مستخدم بالفعل.',
            'mobile.required' => 'رقم الجوال مطلوب.',
            'mobile.numeric' => 'يجب أن يكون رقم الجوال عبارة عن أرقام فقط.',
            'mobile.digits' => 'يجب أن يكون رقم الجوال 9 أرقام.',
            'mobile.regex' => 'رقم الجوال يجب أن يبدأ ب 5 ويتبعه ثمانية أرقام.',
            'mobile.unique' => 'رقم الجوال مستخدم بالفعل.',
            'password.min' => 'يجب أن تحتوي كلمة المرور على 8 أحرف على الأقل.',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',
        ]);

        $user = User::findOrFail($id);
        $user->fname = $request->input('fname');

         if ($user->mobile !== $request->input('mobile')) {
           $user->mobile = '00966' . $request->input('mobile');
        }

        $user->email = $request->input('email');

         if ($request->filled('password')) {
            $user->password = bcrypt($request->input('password'));
        }

        $user->save();

        return redirect()->back()->with('success', 'تم تحديث الملف الشخصي بنجاح.');
    }
    
        public function updateProfileImage(Request $request)
        {
            $request->validate([
                'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
    
            $user = Auth::user();
            $data = $request->all();
    
            if ($request->hasFile('photo')) {
                $file = $request->photo;
                $path = fileUploader($file, 'users');
                $data['photo'] = $path;
                deleteFile($user->photo);
            }
    
            $user->update($data);
    
            return redirect()->back()->with('success', 'تم تحديث الصوره بنجاح');
        }

    public function RemoveProfile()
    {
        $user_id=Auth::user()->id;
        $user = User::findOrFail($user_id);
        $user->is_active = false;
        $user->save();
        auth()->logout();

    // Redirect to the login page or any other page as needed
    return redirect('/')->with('status', trans('removeAccount'));
    }
   
    public function search(Request $request)
    {
        $user = Auth::user();
        $contractsQuery = $user->contracts();
        if ($request->filled('tenant_id')) {
            $contractsQuery->where('tenant_id_num', 'like', '%' . $request->tenant_id . '%');
        }
    
        if ($request->filled('request_number')) {
            $contractsQuery->where('uuid', 'like', '%' . $request->request_number . '%');
        }
    
        if ($request->filled('owner_id')) {
            $contractsQuery->where('property_owner_id_num', 'like', '%' . $request->owner_id . '%');
        }
        $myContracts = $contractsQuery->get();
        return view('website.Contract.myContract', compact('myContracts', 'user'));         
    }
    
 
    public function LastStep(Contract $MyContract)
    {
        $step = $MyContract->step;
        
        if ($step == 5) 
        {
            return redirect()->route('Financial', $MyContract->uuid);
        }
    }

    public function LastContract()
    {
        try {
            $user = auth()->user();
            $contract = Contract::where('user_id', $user->id)
            ->where('is_completed', false)
            ->latest('created_at')
            ->whereNull('real_id')
            ->whereNull('real_units_id')
                ->first();
            if ($contract) {
                return redirect()->route('UnCompleted', $contract);
            } else {
                return redirect()->back()->with('error', trans('website.not-found-contract'));
            }
        } catch (\Exception $e) {
            Log::error('Error fetching the last contract: ' . $e->getMessage());

            return redirect()->back()->with('error', trans('website.error'));
        }

    }

      public function LastStepReal(Contract $MyContract)
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

  
    public function LastContractReal()
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
    public function checkContract($uuid)
{
      try {
            $user = Auth::user();
            $user_id = $user->id;
    
            // Find the contract
            $contract = Contract::where('uuid', $uuid)
                ->where('user_id', $user_id)
                ->firstOrFail();
    
            $contractPeriod = ContractPeriod::findOrFail($contract->contract_term_in_years);
            $accountsHandwrite = Account::first();
            $bankAccounts = BankAccount::all();
            $contract_type = $contract->contract_type;
    
            $Pricing = ServicesPricing::where('contract_type', $contract_type)->get();
    
            
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
             $settings = Setting::first();
            $discountedPrice = $discountedPrice ?? 0;
            $app_price = ($settings->housing_tax ?? 0) + ($settings->commercial_tax ?? 0) + ($settings->application_fees ?? 0);
            
            $totalPriceDetails['total_price'] = $totalContractPrice + $contractPeriod->price + $totalPricing + $app_price;
                   
       
            $totalPrice = (isset($totalPriceDetails['total_price']) ? (float)$totalPriceDetails['total_price'] : 0) ;
 
            $discountedPrice = $discountedPrice ?? 0;
            $priceAfterCoupon = max(0, $totalPrice - $discountedPrice);
            return view('website.Contract.financial_statements', compact(
                'contract',
                'couponActive',
                'accountsHandwrite',
                'settings',
                'bankAccounts',
                'contractCouponUsage',
                'Pricing',
                'contractPeriod',
                'totalPriceDetails',
                'priceAfterCoupon',
                'discountedPrice'
            ));
        } catch (\Exception $e) {

            Log::error('Error in Financial method: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());

        }
    } 


    public function tearms()
    {
        $terms = Page::find(2);
        return view('website.pages.tearms', compact('terms'));
    }



}