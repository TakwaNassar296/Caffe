<?php

namespace App\Http\Controllers\Api;

use App\Traits\ApiResponse;
use App\Models\UserLocation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Api\UserLocationResource;
use App\Http\Requests\Api\Profile\UserLocationRequest;

class UserLocationController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $user_id = Auth::guard('user')->id();

        $locations = UserLocation::where('user_id', $user_id)->get();

        if ($locations->isEmpty()) {
            return $this->successResponse('User Locations is Empty');
        }

        return $this->successResponse('User locations fetched successfully.',  UserLocationResource::collection($locations), 200);
    }

    public function store(UserLocationRequest $request)
    {
        $user_id = Auth::guard('user')->id();
        $data = $request->validated();
        $data['user_id'] = $user_id;
        $location = UserLocation::create($data);

        return $this->successResponse('User location created successfully.', new UserLocationResource($location), 201);
    }

    public function update(UserLocationRequest $request, $id)
    {
        $user_id = Auth::guard('user')->id();

        $location = UserLocation::where('id', $id)
            ->where('user_id', $user_id)->first();

        if (!$location) {
            return $this->errorResponse('User location not found.', 404);
        }

        $data = $request->validated();

        $location->update($data);

        return $this->successResponse('User location updated successfully.', new UserLocationResource($location), 201);
    }

    public function destroy($id)
    {
        $user_id = Auth::guard('user')->id();

        $location = UserLocation::where('id', $id)
            ->where('user_id', $user_id)->first();

        if (!$location) {
            return $this->errorResponse('User location not found.', 404);
        }

        $location->delete();

        return $this->successResponse('User location deleted successfully.');
    }
}