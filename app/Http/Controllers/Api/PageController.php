<?php

namespace App\Http\Controllers\Api;

use App\Models\Page;
use App\Models\Coupon;
use App\Traits\ApiResponse;
use App\Http\Resources\Api\PageResource;
use App\Http\Resources\Api\SupportResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PageController extends Controller
{
    use ApiResponse;

    public function termsConditionsUser()
    {
        $page = Page::where('slug', 'terms-and-conditions-user')->first();
        if (!$page) {
            return $this->errorResponse('Page not found', 404);
        }

        return $this->successResponse('Terms and Conditions', $page);
    }

    public function termsConditionsDriver()
    {
        $page = Page::where('slug', 'terms-and-conditions-driver')->first();
        if (!$page) {
            return $this->errorResponse('Page not found', 404);
        }

        return $this->successResponse('Terms and Conditions', $page);
    }


    public function coupon()
    {
        $coupons = Coupon::where('is_active', true)->get();
        return $this->successResponse('Coupons', $coupons);
    }

    public function support()
    {
        $page = Page::where('slug', 'support')->first();
        if (!$page) {
            return $this->errorResponse('Page not found', 404);
        }

        return $this->successResponse('Support', new SupportResource($page));
    }
}