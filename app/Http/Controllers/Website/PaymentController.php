<?php

namespace App\Http\Controllers\Website;

use \Log;
use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ContractPeriod;
use App\Models\CouponUsage;
use App\Models\Payment;
use App\Models\ServicesPricing;
use App\Models\Setting;
use App\Models\User;
use App\Services\TwilioService;
use Clickpaysa\Laravel_package\Facades\paypage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use TaqnyatSms;

class PaymentController extends Controller
{

 
    
  public function index($uuid, Request $request)

  {
    $user = Auth::user();
    $user_id = $user->id;
    $contract = Contract::where('uuid', $uuid)->where('user_id', $user_id)->firstOrFail();
    
    $Pricing = ServicesPricing::where('contract_type', $contract->contract_type)->get();
    $settings = Setting::first();
    $app_price = ($settings->housing_tax ?? 0) + ($settings->commercial_tax ?? 0) + ($settings->application_fees ?? 0)  ;
    $totalPricing = $Pricing->sum('price')+$app_price; 

  
    $contract_coupon = CouponUsage::where('contract_uuid', $contract->uuid)->first();
 
    $totalContractPrice = $contract_coupon 
        ? $contract_coupon->calculateDiscountedPrice($contract) 
        : ($contract->getPriceContractAttribute() + $totalPricing);

   
        $contractPeriods = ContractPeriod::where('contract_type', $contract->contract_type)->where('id', $contract->contract_term_in_years)->firstOrFail();
    $contractPeriodsPrice = $contractPeriods->price;
    $requestData = [
        "profile_id" => "44794",
        "tran_type" => "sale",
        "tran_class" => "ecom",
        "cart_id" => $contract->uuid, 
        "cart_description" => "Contract " . $contract->uuid,  
        "cart_currency" => "SAR",
        "cart_amount" => $totalContractPrice+$contractPeriodsPrice,
        "return" => route('rating', ['uuid' => $contract->uuid, 'user_id' => $user_id]),
        "callback" => route('updateCartByIPN', ['uuid' => $contract->uuid]),
    ];
    

   $response = Http::withHeaders([
            'Authorization' => 'SGJNLW9BLW-JJBMDRDD6R-B9L2JKDZZD',  
            'Content-Type' => 'application/json',
        ])->post('https://secure.clickpay.com.sa/payment/request', $requestData);
     if ($response->successful()) {
        $paymentData = $response->json();
        return redirect($paymentData['redirect_url']);
    } else {
        return redirect()->back()->with('error', 'غير مسموح');
    }

}
private function formatPhoneNumber($mobile)
{
     $mobile = (string) $mobile;

     $formattedNumber = preg_replace('/^0|\+/', '', $mobile);

     if (!str_starts_with($formattedNumber, '00966')) {
        $formattedNumber = '00966' . $formattedNumber;
    }

    return $formattedNumber;
}

public function updateCartByIPN(Request $requestData, $uuid)
{
     
    $data = $requestData->all();     
  
    $contract = Contract::where('uuid', $uuid)->firstOrFail();

    if ($data['payment_result']['response_status'] == "A") {
     
      
        Payment::create([
            'name' => $data['customer_details']['name'],
            'amount' => $data['cart_amount'],
            'contract_uuid' => $data['cart_id'],
            'tran_currency' => $data['tran_currency'],
            'payment_method' => $data['payment_info']['payment_method'],
            'status' => 'success',
            'payment_date' => now()
        ]);

        
        $contract->is_completed = true;
        $contract->save();

   $formattedMobile = $this->formatPhoneNumber('597500013');
        $body = "قام مستخدم جديد بإنشاء عقد: {$contract->uuid}.";
        $sender = 'AqdiCo';
        $smsId = '25489';

        $this->sendSmsMessage($body, $formattedMobile, $sender, $smsId);

        $recipients = $contract->user->mobile;
        $body = "تم استلام طلبكم رقم : {$contract->uuid}\n" .
                "شكرًا لثقتك. سنعمل على إتمام العقد،\n" .
                "في حال إتمام العقد ستصلكم رسالة من ايجار للموافقة على العقد،\n" .
                "فريق عقدي.";

        $this->sendSmsMessage($body, $recipients, $sender, $smsId);

    } elseif ($data['payment_result']['response_status'] == "D") {
        
        Payment::create([
            'name' => $data['customer_details']['name'],
            'amount' => $data['cart_amount'],
            'contract_uuid' => $data['cart_id'],  
            'tran_currency' => $data['tran_currency'],
            'payment_method' => $data['payment_info']['payment_method'],
            'status' => 'failed',
            'payment_date' => now()
        ]);

       
        $recipients = $contract->user->mobile;
        $body = "عذراً، تعذر إتمام عملية الدفع الخاصة بطلبكم رقم: {$contract->uuid}. الرجاء المحاولة مرة أخرى أو التواصل معنا لمزيد من الدعم.";
        $sender = 'AqdiCo';
        $smsId = '25489';
        $this->sendSmsMessage($body, $recipients, $sender, $smsId);

        // Return error response
        return response()->json(['error' => 'Payment failed. Please try again.'], 400);
    }
}


public function rating($uuid, $user_id)
{
    $user = User::find($user_id);
    Auth::login($user);

    $contract = Contract::where('uuid', $uuid)->where('user_id', $user_id)->firstOrFail();
  
    // Proceed with success message if payment succeeded
    return view('website.Contract.rating', compact('contract', 'user'));
}

   public function sendSmsMessage($body, $recipients, $sender, $smsId){

    $bearer = '5ed5a6f23fb215fa7c1a38ec12f58491';
    $taqnyt = new TaqnyatSms($bearer);
    try {
        $message = $taqnyt->sendMsg($body, $recipients, $sender, $smsId);
        return $message ? true : false;
    } 
    catch (\Exception $e) {
        return 'SMS Error: ' . $e->getMessage();
    }
    }

 
    public function PostRating(Request $request,$uuid,$user_id )
    {
        $user = User::find($user_id);
        Auth::login($user);
        $contract=Contract::where('uuid',$uuid )->where('user_id',$user_id)->first();
        $data = [
            'rating' => $request->rating,
            'rating_note' => $request->rating_note,
        ];

        $contract->update($data);
        $contract->is_completed=true;
          // Retrieve user
        $user = User::find($contract->user_id);

        $payment = Payment::where('contract_uuid', $contract->uuid)->latest()->first();

        // Check if payment is failed
        if ($payment && $payment->status == 'failed') {
            return redirect('/')->with('error', 'عذراً، تعذر إتمام عملية الدفع الخاصة بطلبكم. الرجاء المحاولة مرة أخرى أو التواصل معنا لمزيد من الدعم.');
        }
    
        return redirect('/')->with('success', 'طلبكم رقم ' . $contract->uuid . ' قيد التنفيذ وسيعمل فريقنا على إتمامه 
        وفي حال إتمام الطلب ستصلكم رسالة نصية فريق عقدي.');

   }
  
 
    public function admanPayment()
    {
         $payment = Payment::get();
        
         return view('panel.pages.payment.index', compact('payment'));
    }
}
