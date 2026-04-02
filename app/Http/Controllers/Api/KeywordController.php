<?php

namespace App\Http\Controllers\Api;

use App\Models\Keyword;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class KeywordController extends Controller
{
    use ApiResponse;



    public function index()
    {
        $keywords = Keyword::where('status', 1)->select('id', 'name')->get();
        return $this->successResponse( 'get keywords successfully',$keywords);
    }
}
