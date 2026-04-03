<?php

namespace App\Http\Controllers\Branch;

use App\Models\Admin;
use App\Models\Employee;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Branch\LoginEmployeeRequest;
use App\Http\Resources\Branch\AdminResource as BranchAdminResource;

class AuthController extends Controller
{
    use ApiResponse;

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'fcm_token' => 'nullable|string',
        ]);

        $admin = Admin::where('email', $request->email)
            ->where('role', 'branch_manger')
            ->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return $this->errorResponse('Invalid credentials.', 401);
        }

        if ($request->has('fcm_token')) {
            $admin->update([
                'fcm_token' => $request->fcm_token
            ]);
        }

        // Revoke previous tokens
        $admin->tokens()->delete();

        // Create token
        $token = $admin->createToken('branch-token')->plainTextToken;

        return $this->successResponse(
            'Login successful.',
            [
                'admin' => new BranchAdminResource($admin),
                'token' => $token,
            ],
            200
        );
    }

}
