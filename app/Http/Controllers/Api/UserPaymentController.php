<?php

namespace App\Http\Controllers\Api;

use App\Models\UserPayment;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Api\UserPaymentResource;
use App\Http\Requests\Api\UserPayment\StoreUserCardRequest;

class UserPaymentController extends Controller
{
    use ApiResponse;

    public function store(StoreUserCardRequest $request)
    {
        $user = Auth::guard('user')->user();

        $data = $request->validated();
        $data['user_id'] = $user->id;

        $card = UserPayment::create($data);

        return $this->successResponse('Card saved successfully.', new UserPaymentResource($card));
    }

    public function index()
    {
        $user = Auth::guard('user')->user();

        $cards = UserPayment::where('user_id', $user->id)->get();

        if (!$cards) {
            return $this->successResponse('Your Payment Is Empty.', $cards);
        }

        return $this->successResponse('Cards fetched successfully.', UserPaymentResource::collection($cards));
    }
}
