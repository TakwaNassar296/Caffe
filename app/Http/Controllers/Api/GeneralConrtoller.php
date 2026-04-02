<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GeneralConrtoller extends Controller
{
    public function cities()
    {
        $data=City::where('is_active',true)->get();
        
        if ($data->isEmpty()) {
            return response()->json([
                'status' => true,
                'message' => 'There are no city at the moment',
                'data' => [],
            ], Response::HTTP_OK);
        }

        return response()->json([
            'status' => true,
            'message' => 'cities List',
            'data' =>$data,
        ], Response::HTTP_OK);
    }
}
