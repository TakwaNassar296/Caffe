<?php

namespace App\Http\Controllers\Api;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Advertisement;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdvertisementResource;

class AdvertisementController extends Controller
{
    use ApiResponse;

    public function advertisementText()
    {
        $ads = Advertisement::where('type', 'text')->where('status', 1)->get();

        return $this->successResponse(
            'Text advertisements retrieved successfully',
            AdvertisementResource::collection($ads)
        );
    }

    public function advertisementImage()
    {
        $ads = Advertisement::where('type', 'image')->where('status', 1)->select('id', 'image')->get();

        return $this->successResponse(
            'Image advertisements retrieved successfully',
            AdvertisementResource::collection($ads)
        );
    }

    public function home(){
       $adsText = Advertisement::where('type', 'text')->where('status', 1)->get();
       $adsImage = Advertisement::where('type', 'image')->where('status', 1)->get();
       return $this->successResponse(
           'Home advertisements retrieved successfully',
           [
               'text_ads' => AdvertisementResource::collection($adsText),
               'image_ads' => AdvertisementResource::collection($adsImage)
           ]
       );
    }
}
