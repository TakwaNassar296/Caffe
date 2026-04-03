<?php

namespace App\Http\Controllers\Api\Driver;

use App\Models\Driver;
use App\Models\DriverOtp;
use App\Support\PhoneNumber;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Driver\RegisterStep1Request;
use App\Http\Requests\Api\Driver\RegisterStep2Request;
use App\Http\Requests\Api\Driver\RegisterStep3Request;
use App\Http\Requests\Api\Driver\RegisterStep4Request;
use App\Services\TwilioService;
use Illuminate\Support\Facades\Log;

class DriverRegisterController extends Controller
{
    use ApiResponse;

    protected $twilio;

    public function __construct(TwilioService $twilio)
    {
        $this->twilio = $twilio;
    }

    public function registerStep1(RegisterStep1Request $request)
    {
        $data = $request->validated();
        $data['phone_number'] = PhoneNumber::normalize(
            $request->string('phone_number')->toString(),
            $request->string('country_key')->toString(),
        );

        if (Driver::where('email', $request['email'])->exists()) {
            return $this->errorResponse(__('apis.email_exists'));
        }

        if ($request->hasFile('profile_image')) {
            $data['profile_image'] = $request->file('profile_image')->store('drivers/profile_images', 'public');
        }

        Driver::updateOrCreate(
            ['email' => $request['email']],
            $data
        );

        return $this->successResponse(__('apis.step1_success'));
    }

    public function registerStep2(RegisterStep2Request $request)
    {
        $driver = Driver::where('email', $request->email)->first();

        if (!$driver) {
            return $this->errorResponse(__('apis.step1_required'));
        }

        $data = $request->validated();

        if ($request->hasFile('vehicle_registration_document')) {
            $data['vehicle_registration_document'] = $request->file('vehicle_registration_document')->store('drivers/vehicle_registration', 'public');
        }
        if ($request->hasFile('vehicle_insurance_document')) {
            $data['vehicle_insurance_document'] = $request->file('vehicle_insurance_document')->store('drivers/vehicle_insurance', 'public');
        }

        $driver->update($data);

        return $this->successResponse(__('apis.step2_success'));
    }

    public function registerStep3(RegisterStep3Request $request)
    {
        $driver = Driver::where('email', $request['email'])->first();

        if (!$driver) {
            return $this->errorResponse(__('apis.step1_required'));
        }

        $data = $request->validated();

        if ($request->hasFile('driving_license_photo')) {
            $data['driving_license_photo'] = $request->file('driving_license_photo')->store('drivers/driving_license', 'public');
        }

        $driver->update($data);

        return $this->successResponse(__('apis.step3_success'));
    }

    public function registerStep4(RegisterStep4Request $request)
    {
        $driver = Driver::where('email', $request['email'])->first();

        if (!$driver) {
            return $this->errorResponse(__('apis.step1_required'));
        }

        $driver->update($request->validated());

        $driver->status = "pending";
        $driver->save();

      
        $otp = $driver->generate_code_otp ?? rand(1000, 9999);
        
        $message = __('admin.register_otp_message', [
            'app_name' => config('app.name'),
            'code'     => $otp
        ]);

        $this->sendOtp($driver, $message, $otp);

        return $this->successResponse(__('apis.driver_registered_success'));
    }

    private function sendOtp($driver, $message, $otp)
    {
        try {
            $this->twilio->sendSMS($driver->phone_number, $message);
            Log::info("Driver Registration OTP sent to: " . $driver->phone_number);
        } catch (\Exception $e) {
            Log::error("Twilio Driver Registration Error: " . $e->getMessage());
        }

        DriverOtp::updateOrCreate(
            ['driver_id' => $driver->id],
            [
                'otp' => $otp,
                'last_resend' => now(),
            ]
        );
    }
}