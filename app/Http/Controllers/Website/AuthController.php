<?php

namespace App\Http\Controllers\Website;

use TaqnyatSms;
use Carbon\Carbon;
use App\Models\User;
use App\Models\SmsLog;
use Illuminate\Http\Request;
use App\Services\TwilioService;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    public function signup()
    {
        return view('website.auth.signup');
    }

    private function formatPhoneNumber($mobile)
    {
        $formattedNumber = preg_replace('/^0|\+/', '', $mobile);

        if (!str_starts_with($formattedNumber, '00966')) {

            $formattedNumber = '00966' . $formattedNumber;
        }

        return $formattedNumber;
    }

    // public function sendSmsMessage($body, $recipients, $sender, $smsId)
    // {

    //     $bearer = '5ed5a6f23fb215fa7c1a38ec12f58491';
    //     $taqnyt = new TaqnyatSms($bearer);
    //     try {
    //         $message = $taqnyt->sendMsg($body, $recipients, $sender, $smsId);
    //         return $message ? true : false;
    //     } catch (\Exception $e) {
    //         return 'SMS Error: ' . $e->getMessage();
    //     }
    // }

    // public function postSignup(Request $request)
    // {

    //     $request->validate([

    //         'fname' => 'required|string|max:255',
    //         'email' => 'required|email|unique:users,email',
    //         'password' => 'required|min:8|confirmed',
    //         'mobile' => [
    //             'required',
    //             'min:9',
    //             'regex:/^5[0-9]{8}$/',
    //             function ($attribute, $value, $fail) {
    //                 $formattedMobile = '00966' . $value;
    //                 if (User::where('mobile', $formattedMobile)->exists()) {
    //                     $fail('رقم الجوال مستخدم بالفعل.');
    //                 }
    //             },
    //         ],

    //     ], [
    //         'fname.required' => 'الاسم الأول مطلوب.',
    //         'email.required' => 'البريد الإلكتروني مطلوب.',
    //         'email.email' => 'يجب أن يكون البريد الإلكتروني عنوان بريد إلكتروني صالح.',
    //         'email.unique' => 'هذا البريد الإلكتروني مستخدم بالفعل.',
    //         'mobile.required' => 'رقم الجوال مطلوب.',
    //         'mobile.numeric' => 'يجب أن يكون رقم الجوال عبارة عن أرقام فقط.',
    //         'mobile.min' => 'رقم الجوال 9 ارقام علي الاقل.',
    //         'password.required' => 'كلمة المرور مطلوبة.',
    //         'mobile.digits_between' => 'يجب أن لا يقل عن 8 أرقام ولا يزيد عن 15.',
    //         'mobile.unique' => 'رقم الجوال مستخدم بالفعل.',
    //         'password.required' => 'كلمة المرور مطلوبة.',
    //         'password.min' => 'يجب أن تحتوي كلمة المرور على 8 أحرف على الأقل.',
    //         'password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',
    //         'mobile.regex' => 'رقم الجوال يجب أن يبدأ بـ 5 ويتبعه ثمانية أرقام من 0 إلى 9.',

    //     ]);

    //     $hashedPassword = bcrypt($request->password);
    //     $formattedMobile = $this->formatPhoneNumber($request->mobile);

    //     $user = User::create([
    //         'fname' => $request->fname,
    //         'email' => $request->email,
    //         'mobile' => $formattedMobile,
    //         'password' => $hashedPassword,
    //     ]);

    //     $verificationCode = $user->generateVerificationCode();
    //     $user->verification_code = $verificationCode;
    //     $user->save();

    //     $body = "كود تأكيد حسابك الخاص في عقدي هو: " . $verificationCode;
    //     $sender = 'AqdiCo';
    //     $smsId = '25489';

    //     $smsResult = $this->sendSmsMessage($body, $formattedMobile, $sender, $smsId);

    //     if ($smsResult === true) {
    //         return view('website.auth.verification', compact('user'))
    //             ->with('success', 'تم إنشاء حسابكم بنجاح. يرجى التحقق من رمز التأكيد المرسل.');
    //     } else {
    //         return back()->withErrors(['sms' => 'فشل في إرسال رسالة التحقق، يرجى المحاولة مرة أخرى.']);
    //     }
    // }



    public function sendSmsMessage($body, $recipients, $sender, $smsId, $userId = null , $type = null)
    {
        $bearer = '5ed5a6f23fb215fa7c1a38ec12f58491';
        $taqnyt = new TaqnyatSms($bearer);

        try {
            $message = $taqnyt->sendMsg($body, $recipients, $sender, $smsId);

            // Log success or failure of SMS sending
            SmsLog::create([
                'user_id' => $userId,
                'phone_number' => $recipients,
                'message' => $message ? $body : 'SMS sending failed',
                'sms_id' => $message ? $smsId : null,
                'type' => $type ,
                'sent_at' => now(),
            ]);

            return $message ? true : false;
        } catch (\Exception $e) {
            // Log exception as failure
            SmsLog::create([
                'user_id' => $userId,
                'phone_number' => $recipients,
                'message' => 'SMS Error: ' . $e->getMessage(),
                'sms_id' => null,
                'sent_at' => now(),
            ]);
            return 'SMS Error: ' . $e->getMessage();
        }
    }


    public function postSignup(Request $request)
    {

        $request->validate([

            'fname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'mobile' => [
                'required',
                'min:9',
                'regex:/^5[0-9]{8}$/',
                function ($attribute, $value, $fail) {
                    $formattedMobile = '00966' . $value;
                    if (User::where('mobile', $formattedMobile)->exists()) {
                        $fail('رقم الجوال مستخدم بالفعل.');
                    }
                },
            ],

        ], [
            'fname.required' => 'الاسم الأول مطلوب.',
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email' => 'يجب أن يكون البريد الإلكتروني عنوان بريد إلكتروني صالح.',
            'email.unique' => 'هذا البريد الإلكتروني مستخدم بالفعل.',
            'mobile.required' => 'رقم الجوال مطلوب.',
            'mobile.numeric' => 'يجب أن يكون رقم الجوال عبارة عن أرقام فقط.',
            'mobile.min' => 'رقم الجوال 9 ارقام علي الاقل.',
            'password.required' => 'كلمة المرور مطلوبة.',
            'mobile.digits_between' => 'يجب أن لا يقل عن 8 أرقام ولا يزيد عن 15.',
            'mobile.unique' => 'رقم الجوال مستخدم بالفعل.',
            'password.required' => 'كلمة المرور مطلوبة.',
            'password.min' => 'يجب أن تحتوي كلمة المرور على 8 أحرف على الأقل.',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',
            'mobile.regex' => 'رقم الجوال يجب أن يبدأ بـ 5 ويتبعه ثمانية أرقام من 0 إلى 9.',

        ]);

        $hashedPassword = bcrypt($request->password);
        $formattedMobile = $this->formatPhoneNumber($request->mobile);


        // Check SMS cooldown - last SMS sent to this number in last 2 minutes?
        $lastSms = SmsLog::where('phone_number', $formattedMobile)
            ->orderBy('sent_at', 'desc')
            ->first();

        if ($lastSms && Carbon::parse($lastSms->sent_at)->greaterThan(Carbon::now()->subMinutes(2))) {
            // Log blocked resend attempt
            SmsLog::create([
                'user_id' => null, // no user yet because signup is just starting
                'phone_number' => $formattedMobile,
                'message' => 'Resend blocked: cooldown not expired',
                'type' => 'Website_postSignup',
                'sms_id' => null,
                'sent_at' => now(),
            ]);
            $secondsLeft = 120 - Carbon::now()->diffInSeconds(Carbon::parse($lastSms->sent_at));
            return back()->withErrors(['error' => "يرجى الانتظار $secondsLeft ثانية قبل إعادة إرسال كود التحقق."]);
        }


        $user = User::create([
            'fname' => $request->fname,
            'email' => $request->email,
            'mobile' => $formattedMobile,
            'password' => $hashedPassword,
        ]);

        $verificationCode = $user->generateVerificationCode();
        $user->verification_code = $verificationCode;
        $user->save();

        $body = "كود تأكيد حسابك الخاص في عقدي هو: " . $verificationCode;
        $sender = 'AqdiCo';
        $smsId = '25489';

        // Pass user_id to log SMS with the new user
        $smsResult = $this->sendSmsMessage($body, $formattedMobile, $sender, $smsId, $user->id , 'Website_postSignup');

        if ($smsResult === true) {
            return view('website.auth.verification', compact('user'))
                ->with('success', 'تم إنشاء حسابكم بنجاح. يرجى التحقق من رمز التأكيد المرسل.');
        } else {
            return back()->withErrors(['error' => 'فشل في إرسال رسالة التحقق، يرجى المحاولة مرة أخرى.']);
        }
    }

    public function login()
    {

        return view('website.auth.login');
    }

    // public function postLogin(LoginRequest $request)
    // {

    //     $request->validate([
    //         'mobile' => 'required|numeric|digits:9|regex:/^5[0-9]{8}$/',
    //         'password' => 'required',
    //     ], [
    //         'mobile.required' => 'رقم الجوال مطلوب.',
    //         'mobile.digits' => 'رقم الجوال يجب أن يتكون من 9 أرقام.',
    //         'mobile.regex' => 'رقم الجوال يجب أن يبدأ بـ 5 ويتبعه ثمانية أرقام.',
    //         'password.required' => 'كلمة المرور مطلوبة.',
    //     ]);

    //     $mobile = ltrim($request->mobile, '5');
    //     $formattedMobile = '009665' . $mobile;
    //     $user = User::where('mobile', $formattedMobile)->first();

    //     if (!$user || !Hash::check($request->password, $user->password)) {
    //         return redirect('login')->withErrors(['error' => trans('validation.invalid')]);
    //     }

    //     if (!$user->is_active) {
    //         return redirect('login')->withErrors(['error' => trans('validation.inactive')]);
    //     }

    //     if (!$user->isVerified()) {
    //         $user->generateVerificationCode();
    //         $user->save();
    //         $body = "رمز التحقق: {$user->verificationCode} لدخول منصة aqdi.sa";
    //         $sender = 'AqdiCo';
    //         $smsId = '25489';
    //         $smsResult = $this->sendSmsMessage($body, $formattedMobile, $sender, $smsId);

    //         if ($smsResult === true) {
    //             return view('website.auth.verification', compact('user'))->with('success', 'يرجى التحقق من رمز التأكيد المرسل.');
    //         } else {
    //             return back()->withErrors(['sms' => 'فشل في إرسال رسالة التحقق، يرجى المحاولة مرة أخرى.']);
    //         }
    //     }

    //     if (Auth::attempt(['mobile' => $formattedMobile, 'password' => $request->password], $request->has('remember'))) {
    //         return redirect()->intended('/')->with('success', trans('website.successLogin'));
    //     }

    //     Auth::login($user);
    //     return redirect()->intended('/')->with('success', trans('website.successLogin'));
    // }




    public function postLogin(LoginRequest $request)
    {
        $request->validate([
            'mobile' => 'required|numeric|digits:9|regex:/^5[0-9]{8}$/',
            'password' => 'required',
        ], [
            'mobile.required' => 'رقم الجوال مطلوب.',
            'mobile.digits' => 'رقم الجوال يجب أن يتكون من 9 أرقام.',
            'mobile.regex' => 'رقم الجوال يجب أن يبدأ بـ 5 ويتبعه ثمانية أرقام.',
            'password.required' => 'كلمة المرور مطلوبة.',
        ]);

        $mobile = ltrim($request->mobile, '5');
        $formattedMobile = '009665' . $mobile;
        $user = User::where('mobile', $formattedMobile)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return redirect('login')->withErrors(['error' => trans('validation.invalid')]);
        }

        if (!$user->is_active) {
            return redirect('login')->withErrors(['error' => trans('validation.inactive')]);
        }

        // If user not verified, send verification code (with cooldown check)
        if (!$user->isVerified()) {

            // Check for recent SMS
            $lastSms = SmsLog::where('phone_number', $formattedMobile)
                ->where('user_id', $user->id)
                ->orderBy('sent_at', 'desc')
                ->first();

            if ($lastSms && Carbon::parse($lastSms->sent_at)->greaterThan(Carbon::now()->subMinutes(2))) {
                // Log blocked resend attempt
                SmsLog::create([
                    'user_id' => $user->id,
                    'phone_number' => $formattedMobile,
                    'message' => 'Resend blocked: cooldown not expired',
                    'type' => 'website_postLogin',
                    'sms_id' => null,
                    'sent_at' => now(),
                ]);

                $secondsLeft = 120 - Carbon::now()->diffInSeconds(Carbon::parse($lastSms->sent_at));
                return back()->withErrors(['error' => "يرجى الانتظار $secondsLeft ثانية قبل إعادة إرسال كود التحقق."]);
            }

            $user->generateVerificationCode();
            $user->save();

            $body = "رمز التحقق: {$user->verificationCode} لدخول منصة aqdi.sa";
            $sender = 'AqdiCo';
            $smsId = '25489';

            $smsResult = $this->sendSmsMessage($body, $formattedMobile, $sender, $smsId, $user->id , 'website_postLogin');

            if ($smsResult === true) {
                return view('website.auth.verification', compact('user'))->with('success', 'يرجى التحقق من رمز التأكيد المرسل.');
            } else {
                return back()->withErrors(['error' => 'فشل في إرسال رسالة التحقق، يرجى المحاولة مرة أخرى.']);
            }
        }

        // Proceed to login
        if (Auth::attempt(['mobile' => $formattedMobile, 'password' => $request->password], $request->has('remember'))) {
            return redirect()->intended('/')->with('success', trans('website.successLogin'));
        }

        Auth::login($user);
        return redirect()->intended('/')->with('success', trans('website.successLogin'));
    }


    private function loginFormatPhoneNumber($mobile)
    {
        $defaultCountryCode = '966';

        if (str_starts_with($mobile, '00966')) {
            $mobile = substr($mobile, 0);
        } elseif (str_starts_with($mobile, $defaultCountryCode)) {
            $mobile = substr($mobile, 3);
        }

        if (!str_starts_with($mobile, '0')) {
            $mobile = '0' . $mobile;
        }

        return $mobile;
    }

    public function verification()
    {
        return view('website.auth.verification');
    }

    public function sendVerification(Request $request)
    {
        $rules = [
            'verification_code' => 'required',
        ];

        $this->validate($request, $rules);
        $user = User::where('mobile', $request->mobile)->first();

        if (!$user) {
            return redirect()->back()->with('error', 'المستخدم غير موجود');
        }

        $verificationCode = (string) $user->verification_code;
        $inputCode = (string) $request->verification_code;

        if ($verificationCode !== $inputCode) {
            return view('website.auth.verification', compact('user'));
        }

        $user->email_verified_at = now();
        $user->save();
        Auth::login($user);
        return redirect()->intended('/')->with('success', 'تم التحقق بنجاح');
    }


    public function Sendcode()
    {
        return view('website.auth.send_code');
    }


    // public function postSendCode(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'mobile' => 'required',
    //     ], [
    //         'mobile.required' => 'يرجى إدخال رقم الهاتف.',
    //     ]);

    //     if ($validator->fails()) {
    //         return redirect()->back()
    //             ->withErrors($validator)
    //             ->withInput();
    //     }

    //     try {
    //         $mobile = ltrim($request->mobile, '5');
    //         $formattedMobile = '009665' . $mobile;

    //         $user = User::where('mobile', $formattedMobile)->firstOrFail();

    //         $resetCode = User::generateResetPasswordCode();
    //         $user->reset_password_code = $resetCode;
    //         $user->save();

    //         // Prepare SMS details
    //         $body = "رمز إعادة كلمة المرور هو: " . $resetCode;
    //         $sender = 'AqdiCo';
    //         $smsId = '25489';

    //         // Send the SMS
    //         $smsResult = $this->sendSmsMessage($body, $formattedMobile, $sender, $smsId);

    //         if ($smsResult === true) {

    //             return view('website.auth.forget_password', compact('user'))
    //                 ->with('success', 'تم إرسال رمز إعادة كلمة المرور عبر SMS.');
    //         } else {
    //             return redirect()->back()->withErrors(['sms' => 'فشل في إرسال رسالة التحقق، يرجى المحاولة مرة أخرى.']);
    //         }
    //     } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
    //         return redirect()->back()->withErrors(['mobile' => 'الهاتف غير مرتبط بأي رقم.']);
    //     } catch (\Exception $e) {
    //         Log::error('Error sending reset code: ' . $e->getMessage());
    //         return redirect()->back()->withErrors(['error' => 'حدث خطأ غير متوقع. يرجى المحاولة لاحقًا.']);
    //     }
    // }


 
public function postSendCode(Request $request)
{
    $validator = Validator::make($request->all(), [
        'mobile' => 'required',
    ], [
        'mobile.required' => 'يرجى إدخال رقم الهاتف.',
    ]);

    if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput();
    }

    try {
        $mobile = ltrim($request->mobile, '5');
        $formattedMobile = '009665' . $mobile;

        $user = User::where('mobile', $formattedMobile)->firstOrFail();

        //  Check for cooldown
        $lastSms = SmsLog::where('phone_number', $formattedMobile)
            ->where('user_id', $user->id)
            ->orderBy('sent_at', 'desc')
            ->first();

        if ($lastSms && Carbon::parse($lastSms->sent_at)->greaterThan(Carbon::now()->subMinutes(2))) {
            // Log blocked resend
            SmsLog::create([
                'user_id' => $user->id,
                'phone_number' => $formattedMobile,
                'message' => 'Resend blocked: cooldown not expired',
                'type' => 'website_postSendCode',
                'sms_id' => null,
                'sent_at' => now(),
            ]);
 
            $secondsLeft = 120 - Carbon::now()->diffInSeconds(Carbon::parse($lastSms->sent_at));
            return redirect()->back()->withErrors(['error' => "يرجى الانتظار $secondsLeft ثانية قبل إعادة إرسال الكود."]);
        }

        //  Proceed with generating and sending reset code
        $resetCode = User::generateResetPasswordCode();
        $user->reset_password_code = $resetCode;
        $user->save();

        $body = "رمز إعادة كلمة المرور هو: " . $resetCode;
        $sender = 'AqdiCo';
        $smsId = '25489';

        $smsResult = $this->sendSmsMessage($body, $formattedMobile, $sender, $smsId, $user->id , 'website_postSendCode');

        if ($smsResult === true) {
            return view('website.auth.forget_password', compact('user'))
                ->with('success', 'تم إرسال رمز إعادة كلمة المرور عبر SMS.');
        } else {
            return redirect()->back()->withErrors(['sms' => 'فشل في إرسال رسالة التحقق، يرجى المحاولة مرة أخرى.']);
        }
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return redirect()->back()->withErrors(['mobile' => 'الهاتف غير مرتبط بأي رقم.']);
    } catch (\Exception $e) {
        Log::error('Error sending reset code: ' . $e->getMessage());
        return redirect()->back()->withErrors(['error' => 'حدث خطأ غير متوقع. يرجى المحاولة لاحقًا.']);
    }
}


    public function forgotPassword()
    {
        return view('website.auth.send_code');
    }

    public function postForget(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reset_password_code' => 'required|exists:users,reset_password_code',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors(['reset_password_code' => 'كود التحقق غير صحيح أو منتهي'])
                ->withInput();
        }

        $user = User::where('reset_password_code', $request->reset_password_code)->first();

        if (!$user) {
            return redirect()->back()->with('error', 'كود التحقق غير صحيح أو منتهي');
        }

        return view('website.auth.new_password', compact('user'));
    }

    public function newPassword()
    {
        return view('website.auth.new_password');
    }

    public function sendNewPassword(Request $request)
    {
        $rules = [
            'password' => 'required|string|min:8|confirmed',
        ];

        $this->validate($request, $rules);
        $user = User::where('mobile', $request->mobile)->firstOrFail();
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->intended('/')->with('success', trans('website.successChange'));
    }

    // public function resend(Request $request)
    // {
    //     $request->validate([
    //         'mobile' => 'required|exists:users,mobile',
    //     ], [
    //         'mobile.required' => 'يرجى إدخال رقم الهاتف.',
    //         'mobile.exists' => 'رقم الهاتف غير مرتبط بأي حساب.',
    //     ]);

    //     try {

    //         $user = User::where('mobile', $request->mobile)->firstOrFail();

    //         $formattedMobile = $this->formatPhoneNumber($request->mobile);
    //         if (!$formattedMobile) {
    //             return redirect()->back()->withErrors(['mobile' => 'رقم الهاتف غير صالح.']);
    //         }

    //         $body = "رمز التاكيد هو : " . $user->verification_code;
    //         $sender = 'AqdiCo';
    //         $smsId = '25489';

    //         $smsResult = $this->sendSmsMessage($body, $formattedMobile, $sender, $smsId);

    //         if ($smsResult) {
    //             return view('website.auth.verification', compact('user'))
    //                 ->with('success', 'تم ارسال كود التحقق الى الهاتف.');
    //         } else {
    //             return redirect()->back()->withErrors(['sms' => 'فشل في إرسال كود التحقق، يرجى المحاولة لاحقًا.']);
    //         }
    //     } catch (\Exception $e) {
    //         Log::error('Error resending verification code: ' . $e->getMessage());
    //         return redirect()->back()->withErrors(['error' => 'حدث خطأ غير متوقع. يرجى المحاولة لاحقًا.']);
    //     }
    // }


 
public function resend(Request $request)
{
    $request->validate([
        'mobile' => 'required|exists:users,mobile',
    ], [
        'mobile.required' => 'يرجى إدخال رقم الهاتف.',
        'mobile.exists' => 'رقم الهاتف غير مرتبط بأي حساب.',
    ]);

    try {
        $user = User::where('mobile', $request->mobile)->firstOrFail();

        $formattedMobile = $this->formatPhoneNumber($request->mobile);
        if (!$formattedMobile) {
            return redirect()->back()->withErrors(['mobile' => 'رقم الهاتف غير صالح.']);
        }

        //  Check for cooldown
        $lastSms = SmsLog::where('phone_number', $formattedMobile)
            ->where('user_id', $user->id)
            ->orderBy('sent_at', 'desc')
            ->first();

        if ($lastSms && Carbon::parse($lastSms->sent_at)->greaterThan(Carbon::now()->subMinutes(2))) {
            // Log blocked resend
            SmsLog::create([
                'user_id' => $user->id,
                'phone_number' => $formattedMobile,
                'message' => 'Resend blocked: cooldown not expired',
                'type' => 'website_resend',
                'sms_id' => null,
                'sent_at' => now(),
            ]);

            $secondsLeft = 120 - Carbon::now()->diffInSeconds(Carbon::parse($lastSms->sent_at));
            return redirect()->back()->withErrors(['error' => "يرجى الانتظار $secondsLeft ثانية قبل إعادة الإرسال."]);
        }

        //  Send verification SMS
        $body = "رمز التاكيد هو : " . $user->verification_code;
        $sender = 'AqdiCo';
        $smsId = '25489';

        $smsResult = $this->sendSmsMessage($body, $formattedMobile, $sender, $smsId, $user->id , 'website_resend');

        if ($smsResult) {
            return view('website.auth.verification', compact('user'))
                ->with('success', 'تم ارسال كود التحقق الى الهاتف.');
        } else {
            return redirect()->back()->withErrors(['sms' => 'فشل في إرسال كود التحقق، يرجى المحاولة لاحقًا.']);
        }
    } catch (\Exception $e) {
        Log::error('Error resending verification code: ' . $e->getMessage());
        return redirect()->back()->withErrors(['error' => 'حدث خطأ غير متوقع. يرجى المحاولة لاحقًا.']);
    }
}




    public function logout()
    {
        Auth::logout();
        return redirect('login');
    }
}
