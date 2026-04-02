<?php

namespace App\Http\Controllers\Api\Driver;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Api\ProfileResource;
use App\Http\Requests\Driver\Profile\UpdateProfileRequest;
use App\Http\Resources\Api\Driver\profileDriverResource;

class ProfileController extends Controller
{
    use \App\Traits\ApiResponse;
    public function show(Request $request)
    {
        $driver = Auth::guard('driver')->user();

        if (!$driver) {
            return $this->errorResponse(__('apis.driver_not_found'), 404);
        }

        return $this->successResponse(__('apis.profile_fetched'), new profileDriverResource($driver), 200);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {

        $driver = Auth::guard('driver')->user();

        if (!$driver) {
            return $this->errorResponse(__('apis.driver_not_found'), 404);
        }

        $driver->update($request->only(['first_name', 'last_name', 'phone_number']));

        if ($request->hasFile('image')) {
            if ($driver->profile_image && Storage::disk('public')->exists($driver->profile_image)) {
                Storage::disk('public')->delete($driver->profile_image);
            }


            $path = $request->file('image')->store('profile_images', 'public');

            $driver->profile_image = $path;
        }

        if ($request->has('new_password')) {
            $driver->password = bcrypt($request['new_password']);
        }

        $driver->save();

        return $this->successResponse(__('apis.profile_updated'), new profileDriverResource($driver), 200);
    }

    public function logout(Request $request)
    {
        $driver = Auth::guard('driver')->user();
        $driver->fcm_token = null;
        $driver->save();
        return $this->successResponse(__('apis.logout_success'));
    }
}
