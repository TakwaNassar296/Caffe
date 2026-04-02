<?php

namespace App\Traits;

trait ApiResponse
{
    public function successResponse($message, $data = [], $status = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public function errorResponse($message, $status = 422, $data = [])
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => $data,
        ], $status);
    }


    public function customResponse($message, $status = 422, $key = null, $data = [])
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'key' => $key,
            'data' => $data,
        ], $status);
    }

    public function PaginationResponse($result, $message = '', $code = 200)
    {
        $response = [
            'status' => true,
            'message' => $message,
            'data' => $result,
            'pagination' => [
                'currentPage' => $result->currentPage(),
                'lastPage' => $result->lastPage(),
                'perPage' => $result->perPage(),
                'total' => $result->total(),
            ]
        ];
        return response()->json($response, $code);
    }
}
