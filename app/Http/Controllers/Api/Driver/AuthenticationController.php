<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\VerifOtpRequest;
use App\Http\Requests\Drive\Auth\ForgetPasswordRequest;
use App\Http\Requests\Drive\Auth\LoginRequest;
use App\Http\Requests\Drive\Auth\ResetPasswordRequest;
use App\Http\Resources\Api\Driver\DriverResource;
use App\Models\Driver;
use App\Models\DriverOtp;
use App\Models\VehicleType;
use App\Services\TwilioService;
use App\Support\PhoneNumber;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthenticationController extends Controller
{
    use ApiResponse;

    protected $twilio;

    public function __construct(TwilioService $twilio)
    {
        $this->twilio = $twilio;
    }

    public function login(LoginRequest $request)
    {
        $phoneNumber = PhoneNumber::normalize(
            $request->string('phone_number')->toString(),
            $request->string('country_key')->toString(),
        );

        $driver = Driver::where('phone_number', $phoneNumber)->first();

        if (!$driver || !Hash::check($request['password'], $driver->password)) {
            return $this->errorResponse(__('apis.invalid_credentials'), 401);
        }

        if (!$driver->account_verified_at) {
            return $this->customResponse(__('apis.account_not_verified'), 403, 'verify_otp');
        }

        if ($driver->status !== 'accepted') {
            return $this->errorResponse(__('apis.driver_not_accepted'), 403);
        }

        if ($request['fcm_token']) {
            $driver->update(['fcm_token' => $request['fcm_token']]);
        }

        $token = $driver->createToken('driver_token')->plainTextToken;

        return $this->successResponse(__('apis.login_success'), [
            'driver' => new DriverResource($driver),
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

        $driver = Driver::where('phone_number', $phoneNumber)->first();


        if (!$driver) {
            return $this->errorResponse(__('apis.driver_not_found'), 404);
        }

        $latestOtp = DriverOtp::where('driver_id', $driver->id)->latest()->first();

        if (!$latestOtp || $latestOtp->otp === null || $latestOtp->otp !== $request['otp']) {
            return $this->errorResponse(__('apis.invalid_otp'));
        }

        $latestOtp->update(['otp' => null]);
        $driver->update(['account_verified_at' => now()]);

        return $this->successResponse(__('apis.phone_verified_wait'), []);
    }

    public function resendOtp(ForgetPasswordRequest $request)
    {
        $phoneNumber = PhoneNumber::normalize(
            $request->string('phone_number')->toString(),
            $request->string('country_key')->toString(),
        );

        $driver = Driver::where('phone_number', $phoneNumber)->first();

        if (!$driver) return $this->errorResponse(__('apis.driver_not_found'), 404);

        $otp = $driver->generate_code_otp ?? rand(1000, 9999);
        
        $message = __('admin.resend_otp_message', [
            'app_name' => config('app.name'),
            'code'     => $otp
        ]);

        $this->sendOtp($driver, $message, $otp);

        return $this->successResponse(__('apis.otp_resent'));
    }

    public function forgetPassword(ForgetPasswordRequest $request)
    {
        $phoneNumber = PhoneNumber::normalize(
            $request->string('phone_number')->toString(),
            $request->string('country_key')->toString(),
        );

        $driver = Driver::where('phone_number', $phoneNumber)->first();

        if (!$driver) return $this->errorResponse(__('apis.driver_not_found'), 404);

        $otp = $driver->generate_code_otp ?? rand(1000, 9999);
        
        $message = __('admin.forget_password_otp_message', [
            'app_name' => config('app.name'),
            'code'     => $otp
        ]);

        $this->sendOtp($driver, $message, $otp);

        return $this->successResponse(__('apis.otp_sent'));
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $phoneNumber = PhoneNumber::normalize(
            $request->string('phone_number')->toString(),
            $request->string('country_key')->toString(),
        );

        $driver = Driver::where('phone_number', $phoneNumber)->first();

        if (!$driver) return $this->errorResponse(__('apis.driver_not_found'), 404);

        $latestOtp = DriverOtp::where('driver_id', $driver->id)->latest()->first();

        if (!$latestOtp || $latestOtp->otp === null || $latestOtp->otp !== $request['otp']) {
             return $this->errorResponse(__('apis.invalid_otp'));
        }

        $driver->update(['password' => bcrypt($request['new_password'])]);
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

    private function sendOtp($driver, $message, $otp)
    {
        try {
            $this->twilio->sendSMS($driver->phone_number, $message);
        } catch (\Exception $e) {
            Log::error("Twilio Driver SMS Error: " . $e->getMessage());
        }

        DriverOtp::updateOrCreate(
            ['driver_id' => $driver->id],
            ['otp' => $otp, 'last_resend' => now()]
        );
    }

    public function vehicleType()
    {
        $vehicleTypes = VehicleType::select('id', 'name', 'icon')->get();
        return $this->successResponse(__('apis.vehicle_types_retrieved'), $vehicleTypes);
    }
}