<?php

namespace App\Http\Controllers\Api\Driver;

use App\Models\Review;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Driver\ReviewResource;

class ReviewController extends Controller
{
    use ApiResponse;

    public function index(){
        $driver = auth()->guard('driver')->user();
        $reviews = Review::where('reviewable_type' , 'App\Models\Driver')->where('reviewable_id', $driver->id)->paginate(10);

        if($reviews->count() ==0 ){
            return $this->successResponse('Reviews Is Empty');
        }
    
      return $this->PaginationResponse(ReviewResource::collection( $reviews) ,'Your Reviews Fetched' );
    }
}
