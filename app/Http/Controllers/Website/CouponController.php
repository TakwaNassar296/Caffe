<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Coupon;
use App\Models\CouponUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CouponController extends Controller
{
    public function index($uuid)
    {
        $contract = Contract::where('uuid', $uuid)->firstOrFail();
        
        return view('website.Contract.coupon', compact('contract'));
    }
    

    public function getCoupon(Request $request, $uuid)
    {
        $request->validate([
            'code_coupon' => 'required',
        ]);

        try {
            $contract = Contract::where('uuid', $uuid)->firstOrFail();

            $contract_coupon = Coupon::where('is_delete',0)->where('code_coupon', $request->code_coupon)
                ->where('date_start', '<=', now())
                ->where('date_end', '>=', now())
                ->first();

            if (!$contract_coupon) {
                return redirect()->back()->with('error', 'الكود غير صحيح');
            }

            $user = Auth::user();
            $usage_limit = $contract_coupon->usage_of_user;

            if ($contract_coupon->usage > 0) {
                $userCouponUsageCount = CouponUsage::where('user_id', $user->id)
                    ->where('coupon_id', $contract_coupon->id)
                    ->count();

                $userContractCouponUsageCount = CouponUsage::where('user_id', $user->id)
                    ->where('coupon_id', $contract_coupon->id)
                    ->where('contract_uuid', $contract->uuid)
                    ->count();

                if ($userCouponUsageCount >= $usage_limit) {
                    return redirect()->route('Financial', ['uuid' => $contract->uuid])->with('error', 'تم تجاوز حد استخدام الكوبون');
                }

                if ($userContractCouponUsageCount > 0) {
                    return redirect()->route('Financial', ['uuid' => $contract->uuid])->with('error', 'لقد استخدمت هذا الكوبون لهذا العقد بالفعل');
                }

                $contract_coupon->decrement('usage');

                CouponUsage::create([
                    'user_id' => $user->id,
                    'coupon_id' => $contract_coupon->id,
                    'contract_uuid' => $contract->uuid,
                    'used_at' => now(),
                ]);

                return redirect()->route('Financial', ['uuid' => $contract->uuid])->with('success', 'تم الخصم بنجاح');
            } else {
                return redirect()->back()->with('error', 'تم تجاوز حد استخدام الكوبون');
            }
        } catch (\Exception $e) {
            Log::error('Error applying coupon: ' . $e->getMessage());
            return redirect()->back()->with('error', 'حدث خطأ أثناء تطبيق الكوبون. الرجاء المحاولة مرة أخرى.');
        }
    }
    }
    
 
 