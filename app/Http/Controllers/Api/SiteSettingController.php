<?php

namespace App\Http\Controllers\Api;

use App\Models\SiteSetting;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\SiteSettingResource;

class SiteSettingController extends Controller
{
       use ApiResponse;

    public function index()
    {
        $siteSettings = SiteSetting::first();
        if (!$siteSettings) {
            return $this->errorResponse('Site settings not found.', 404);
        }

        return $this->successResponse('Site settings fetched successfully.', new SiteSettingResource($siteSettings));
    }
}
