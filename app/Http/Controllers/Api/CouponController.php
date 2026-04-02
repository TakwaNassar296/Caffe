<?php

namespace App\Http\Controllers\Api;

use App\Models\Coupon;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CouponController extends Controller
{
     use ApiResponse;
    public function verify(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string|exists:coupons,code',
        ]);

        $coupon = Coupon::where('code', $request->input('coupon_code'))->first();

            if (!$coupon || !$coupon->isValid()) {
                return $this->errorResponse('Invalid or expired coupon code.', 422);
            }

            $data = [
                'type' => $coupon->type,
                'value' => $coupon->value,
            ];

        return $this->successResponse('Coupon code is valid.', $data);
    }
}
