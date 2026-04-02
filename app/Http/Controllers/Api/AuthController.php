<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\ForgetPasswordRequest;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Requests\Api\Auth\ResetPasswordRequest;
use App\Http\Requests\Api\Auth\VerifOtpRequest;
use App\Http\Resources\UserResource;
use App\Models\RefreshToken;
use App\Models\User;
use App\Models\VerificationOtp;
use App\Services\TwilioService;
use App\Support\PhoneNumber;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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

        $user = User::create([
            'first_name' => $request['first_name'],
            'last_name'  => $request['last_name'],
            'phone_number' => $phoneNumber,
            'password'   => bcrypt($request['password']),
        ]);

        $this->sendOtp($user);

        return $this->successResponse(__('apis.user_registered'), [], 201);
    }

    public function login(LoginRequest  $request)
    {
        $phoneNumber = PhoneNumber::normalize(
            $request->string('phone_number')->toString(),
            $request->string('country_key')->toString(),
        );

        $user = User::where('phone_number', $phoneNumber)
            ->orWhere('phone_number', $request['phone_number'])
            ->first();

        if (!$user || !Hash::check($request['password'], $user->password)) {
            return $this->errorResponse(__('apis.invalid_credentials'), 401);
        }

        if ($user->account_verified_at == null) {
            return $this->customResponse(__('apis.verify_phone'), 403, 'verify_otp');
        }

        if ($request['fcm_token']) {
            $user->fcm_token = $request['fcm_token'];
            $user->save();
        }

        //   $refreshToken = null;
        // if ($request->['remember_me']) {
        //     $refreshToken = Str::random(64);
        //     RefreshToken::create([
        //         'user_id' => $user->id,
        //         'token' => $refreshToken,
        //         'expires_at' => now()->addDays(30),
        //     ]);
        // }

        // return response()->json([
        //     'status' => true,
        //     'message' => 'Login successful.',
        //     'data' => [
        //         'access_token' => $token,
        //         'refresh_token' => $refreshToken,
        //     ],
        // ]);

        return $this->successResponse(__('apis.login_success'), new UserResource($user), 200);
    }


    public function verifyOtp(VerifOtpRequest $request)
    {
        $phoneNumber = PhoneNumber::normalize(
            $request->string('phone_number')->toString(),
            $request->string('country_key')->toString(),
        );

        $user = User::where('phone_number', $phoneNumber)
            ->orWhere('phone_number', $request['phone_number'])
            ->first();

        if (!$user) {
            return $this->errorResponse(__('apis.user_not_found'), 404);
        }

        $latestOtp = VerificationOtp::where('user_id', $user->id)
            ->latest()->first();

        if (!$latestOtp || $latestOtp->otp !== $request['otp']) {
            return $this->errorResponse(__('apis.invalid_otp'));
        }

        $latestOtp->update(['otp' => null]);
        $user->account_verified_at = now();
        $user->save();

        return $this->successResponse(__('apis.phone_verified'), new UserResource($user), 200);
    }


    public function resendOtp(ForgetPasswordRequest $request)
    {
        $phoneNumber = PhoneNumber::normalize(
            $request->string('phone_number')->toString(),
            $request->string('country_key')->toString(),
        );

        $user = User::where('phone_number', $phoneNumber)
            ->orWhere('phone_number', $request['phone_number'])
            ->first();

        if (!$user) {
            return $this->errorResponse(__('apis.user_not_found'), 404);
        }

        $lastOtp = VerificationOtp::where('user_id', $user->id)->latest()->first();

        // if ($lastOtp && $lastOtp->last_resend > now()->subMinutes(30)) {     /// time otp is here
        //     return $this->errorResponse('You can only request OTP every 30 minutes.');
        // }

        if (!$lastOtp) {
            return $this->errorResponse(__('apis.otp_not_found'));
        }

        $this->sendOtp($user);

        return $this->successResponse(__('apis.otp_resent'));
    }


    public function forgetPassword(ForgetPasswordRequest $request)
    {
        $phoneNumber = PhoneNumber::normalize(
            $request->string('phone_number')->toString(),
            $request->string('country_key')->toString(),
        );

        $user = User::where('phone_number', $phoneNumber)
            ->orWhere('phone_number', $request['phone_number'])
            ->first();

        $lastOtp = VerificationOtp::where('user_id', $user->id)->latest()->first();

        if (!$lastOtp) {
            return $this->errorResponse(__('apis.otp_not_found'));
        }


        $this->sendOtp($user);

        return $this->successResponse(__('apis.otp_sent'));
    }


    public function resetPassword(ResetPasswordRequest $request)
    {
        $phoneNumber = PhoneNumber::normalize(
            $request->string('phone_number')->toString(),
            $request->string('country_key')->toString(),
        );

        $user = User::where('phone_number', $phoneNumber)
            ->orWhere('phone_number', $request['phone_number'])
            ->first();

        if (!$user) return $this->errorResponse(__('apis.user_not_found'), 404);

        $latestOtp = VerificationOtp::where('user_id', $user->id)
            ->latest()
            ->first();

        if (!$latestOtp || $latestOtp->otp !== $request['otp']) {
            return $this->errorResponse(__('apis.invalid_otp'));
        }

        $user->password = bcrypt($request['new_password']);
        $user->save();

        return $this->successResponse(__('apis.password_reset'));
    }

    public function logout(Request $request)
    {
        $user = Auth::guard('user')->user();

        if (!$user) {
            return $this->errorResponse(__('apis.unauthorized'), 401);
        }

        return $this->successResponse(__('apis.logout_success'));
    }

    private function sendOtp($user)
    {
        $otp = $user->generate_code_otp;
        $message = __('admin.otp_message', ['code' => $otp]);

        try {
            $this->twilio->sendSMS($user->phone_number, $message);
        } catch (\Exception $e) {
            Log::error("Twilio SMS Error: " . $e->getMessage());
        }

        VerificationOtp::create([
            'user_id' => $user->id,
            'otp' => $otp,
            'last_resend' => now(),
        ]);
    }

    public function refreshToken(Request $request)
    {
        $request->user()->tokens()->delete();

        $access_token = $request->user()->createToken('api')->plainTextToken;

        return $this->successResponse(['access_token' => $access_token], __('apis.token_refreshed'));
    }


    public function deleteAccount(Request $request)
    {
        $user = Auth::guard('user')->user();

        if (!$user) {
            return $this->errorResponse(__('apis.unauthorized'), 401);
        }

        $user->delete();

        return $this->successResponse(__('apis.account_deleted'));
    }

    


    // public function refreshToken(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'refresh_token' => 'required|string',
    //     ]);

    //     if ($validator->fails()) {
    //         return$this->errorResponse($validator->errors()->first());
    //     }

    //     $refresh_token = RefreshToken::where('token', $request->refresh_token)->first();

    //     if (!$refresh_token || $refresh_token->expires_at < now()) {
    //         return$this->errorResponse('Invalid or expired refresh token.', 401);
    //     }

    //     $accessToken = $refresh_token->user->createToken('access_token')->plainTextToken;

    //     return $this->successResponse('Token refreshed successfully.', [
    //         'access_token' => $accessToken,
    //     ]);
    // }

    //     private function generateRefreshToken($user)
    //     {
    //         return RefreshToken::create([
    //             'user_id' => $user->id,
    //             'token' => Str::random(64),
    //             'expires_at' => now()->addDays(30),
    //         ]);
    //     }
}
