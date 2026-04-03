<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\ForgetPasswordRequest;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Requests\Api\Auth\ResetPasswordRequest;
use App\Http\Requests\Api\Auth\VerifOtpRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\VerificationOtp;
use App\Services\TwilioService;
use App\Support\PhoneNumber;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    use ApiResponse;

    protected $twilio;

    public function __construct(TwilioService $twilio)
    {
        $this->twilio = $twilio;
    }

    public function register(RegisterRequest $request)
    {
        $phoneNumber = PhoneNumber::normalize(
            $request->string('phone_number')->toString(),
            $request->string('country_key')->toString(),
        );

        return DB::transaction(function () use ($request, $phoneNumber) {
            $user = User::create([
                'first_name'   => $request['first_name'],
                'last_name'    => $request['last_name'],
                'phone_number' => $phoneNumber,
                'password'     => bcrypt($request['password']),
            ]);

            $otp = $user->generate_code_otp ?? rand(1000, 9999);
            
            $message = __('admin.register_otp_message', [
                'app_name' => config('app.name'),
                'code'     => $otp
            ]);

            $this->sendOtp($user, $message, $otp);

            return $this->successResponse(__('apis.user_registered'), [], 201);
        });
    }

    public function login(LoginRequest $request)
    {
        $phoneNumber = PhoneNumber::normalize(
            $request->string('phone_number')->toString(),
            $request->string('country_key')->toString(),
        );

        $user = User::where('phone_number', $phoneNumber)->first();

        if (!$user || !Hash::check($request['password'], $user->password)) {
            return $this->errorResponse(__('apis.invalid_credentials'), 401);
        }

        if ($user->account_verified_at == null) {
            return $this->customResponse(__('apis.verify_phone'), 403, 'verify_otp');
        }

        if ($request['fcm_token']) {
            $user->update(['fcm_token' => $request['fcm_token']]);
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return $this->successResponse(__('apis.login_success'), [
            'user' => new UserResource($user),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }

    public function verifyOtp(VerifOtpRequest $request)
    {
        $phoneNumber = PhoneNumber::normalize(
            $request->string('phone_number')->toString(),
            $request->string('country_key')->toString(),
        );

        $user = User::where('phone_number', $phoneNumber)->first();

        if (!$user) {
            return $this->errorResponse(__('apis.user_not_found'), 404);
        }

        $latestOtp = VerificationOtp::where('user_id', $user->id)->latest()->first();

        if (!$latestOtp || $latestOtp->otp === null || $latestOtp->otp !== $request['otp']) {
            return $this->errorResponse(__('apis.invalid_otp'));
        }

        $user->update(['account_verified_at' => now()]);
        $latestOtp->update(['otp' => null]);

        $token = $user->createToken('api_token')->plainTextToken;

        return $this->successResponse(__('apis.phone_verified'), [
            'user' => new UserResource($user),
            'access_token' => $token
        ], 200);
    }

    public function resendOtp(ForgetPasswordRequest $request)
    {
        $phoneNumber = PhoneNumber::normalize(
            $request->string('phone_number')->toString(),
            $request->string('country_key')->toString(),
        );

        $user = User::where('phone_number', $phoneNumber)->first();

        if (!$user) {
            return $this->errorResponse(__('apis.user_not_found'), 404);
        }

        $otp = $user->generate_code_otp ?? rand(1000, 9999);
        
        $message = __('admin.resend_otp_message', [
            'app_name' => config('app.name'),
            'code'     => $otp
        ]);

        $this->sendOtp($user, $message, $otp);

        return $this->successResponse(__('apis.otp_resent'));
    }

    public function forgetPassword(ForgetPasswordRequest $request)
    {
        $phoneNumber = PhoneNumber::normalize(
            $request->string('phone_number')->toString(),
            $request->string('country_key')->toString(),
        );

        $user = User::where('phone_number', $phoneNumber)->first();

        if (!$user) {
            return $this->errorResponse(__('apis.user_not_found'), 404);
        }

        $otp = $user->generate_code_otp ?? rand(1000, 9999);
        
        $message = __('admin.forget_password_otp_message', [
            'app_name' => config('app.name'),
            'code'     => $otp
        ]);

        $this->sendOtp($user, $message, $otp);

        return $this->successResponse(__('apis.otp_sent'));
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $phoneNumber = PhoneNumber::normalize(
            $request->string('phone_number')->toString(),
            $request->string('country_key')->toString(),
        );

        $user = User::where('phone_number', $phoneNumber)->first();

        if (!$user) return $this->errorResponse(__('apis.user_not_found'), 404);

        $latestOtp = VerificationOtp::where('user_id', $user->id)->latest()->first();

        if (!$latestOtp || $latestOtp->otp === null || $latestOtp->otp !== $request['otp']) {
            return $this->errorResponse(__('apis.invalid_otp'));
        }

        $user->update(['password' => bcrypt($request['new_password'])]);
        $latestOtp->update(['otp' => null]);

        return $this->successResponse(__('apis.password_reset'));
    }

    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }

        return $this->successResponse(__('apis.logout_success'));
    }

    private function sendOtp($user, $message, $otp)
    {
        try {
            $this->twilio->sendSMS($user->phone_number, $message);
            Log::info("OTP sent to: " . $user->phone_number);
        } catch (\Exception $e) {
            Log::error("Twilio Error: " . $e->getMessage());
        }

        VerificationOtp::updateOrCreate(
            ['user_id' => $user->id],
            [
                'otp' => $otp,
                'last_resend' => now(),
            ]
        );
    }

    public function refreshToken(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();
        $access_token = $user->createToken('api_token')->plainTextToken;

        return $this->successResponse([
            'access_token' => $access_token,
            'token_type' => 'Bearer',
        ], __('apis.token_refreshed'));
    }

    public function deleteAccount(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->tokens()->delete();
            $user->delete();
        }

        return $this->successResponse(__('apis.account_deleted'));
    }
}