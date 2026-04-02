<?php

namespace App\Http\Traits;

trait Responser
{

    protected function successMessage($msg, $code = 200)
    {
        return response()->json([
            'message' => $msg,
            'code' => $code,
            'success' => true,
        ]);
    }

    protected function errorMessage($msg, $code = 400)
    {
        return response()->json([
            'message' => $msg,
            'code' => $code,
            'success' => false,
        ]);
    }

    protected function errorResponse($data, $code)
    {
        return response()->json([
            'code' => $code,
            'success' => false,
            'errors' => $data,
        ]);
    }

    protected function apiResponse($data, $msg, $code = 200)
    {
        return response()->json([
            'message' => $msg,
            'code' => $code,
            'success' => true,
            'data' => $data,
        ]);
    }

    protected function paginate($object)
    {
        return [
            'current_page' => $object->currentPage(),
            'last_page' => $object->lastPage(),
            'first_page_url' => $object->url(1),
            'last_page_url' => $object->url($object->lastPage()),
            'next_page_url' => $object->nextPageUrl(),
            'prev_page_url' => $object->previousPageUrl(),
            'from' => $object->firstItem(),
            'to' => $object->lastItem(),
            'per_page' => $object->perPage(),
            'total' => $object->total(),
        ];
    }
}
