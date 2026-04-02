<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Api\Profile\UpdateProfileRequest;
use App\Http\Resources\Api\ProfileResource;

class ProfileController extends Controller
{
    use \App\Traits\ApiResponse;
    public function show(Request $request)
    {
        $user = Auth::guard('user')->user();

        if (!$user) {
            return $this->errorResponse('User not found.', 404);
        }

        return $this->successResponse('Profile fetched successfully.', new ProfileResource($user), 200);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = Auth::guard('user')->user();

        if (!$user) {
            return $this->errorResponse('User not found.', 404);
        }

        $user->update($request->only(['first_name', 'last_name', 'phone_number']));

        if ($request->hasFile('image')) {
            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }

            $path = $request->file('image')->store('profile_images', 'public');
            $user->image = $path;
        }

        if ($request->has('new_password')) {
            $user->password = bcrypt($request['new_password']);
        }

        $user->save();

        return $this->successResponse('Profile updated successfully.', new ProfileResource($user), 200);
    }
}
