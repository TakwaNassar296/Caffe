<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\Responser;
use App\Models\Contract;
use App\Models\ContractPeriod;
use App\Models\CouponUsage;
use App\Models\Coupon;
use App\Models\Payment;
use App\Models\ServicesPricing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use TaqnyatSms;

class PaymentController extends Controller
{
    use Responser;

    public function index(Request $request, $uuid)
    {
        $contract = Contract::where('uuid', $uuid)->firstOrFail();
    
 
        $pricing = ServicesPricing::where('contract_type', $contract->contract_type)->get();
        $totalPricing = $pricing->sum('price');
    
        // Calculate total contract price
        $totalContractPrice = $contract->getPriceContractAttribute() + $totalPricing;
    
        // Apply coupon discount
        $couponAmount = 0;
        $contractCoupon = CouponUsage::where('contract_uuid', $contract->uuid)->first();
        if ($contractCoupon) {
            $coupon = Coupon::find($contractCoupon->coupon_id);
            if ($coupon) {
                $couponAmount = ($coupon->type_coupon === 'ratio') 
                    ? ($totalContractPrice * $coupon->value_coupon / 100)
                    : $coupon->value_coupon;
            }
        }
    
        $totalContractPrice = max(0, $totalContractPrice - $couponAmount);
    
 
        $contractPeriod = ContractPeriod::where('contract_type', $contract->contract_type)
            ->where('id', $contract->contract_term_in_years)
            ->firstOrFail();
        $contractPeriodPrice = max(0, $contractPeriod->price);
    
       
        $cartAmount = max(0, $totalContractPrice + $contractPeriodPrice);
    
      // Prepare payment request payload
        $requestData = [
            "profile_id"       => '44794',
            "tran_type"        => "sale",
            "tran_class"       => "ecom",
            "cart_id"          => $contract->uuid . '-' . now()->timestamp,
            "cart_description" => "Contract " . $contract->uuid,
            "cart_currency"    => "SAR",
            "cart_amount"      => $cartAmount,
            "callback"         => route('callback', ['uuid' => $contract->uuid]),
        ];
    
        $headers = [
            'Authorization' => 'SGJNLW9BLW-JJBMDRDD6R-B9L2JKDZZD',
            'Content-Type'  => 'application/json',
        ];
    
        $response = Http::withHeaders($headers)->post('https://secure.clickpay.com.sa/payment/request', $requestData);
        $paymentData = $response->json();
    
        if (!isset($paymentData['redirect_url'])) {
            return response()->json(['message' => trans('api.not_accept')], 400);
        }
    
        return response()->json(['Payment_url' => $paymentData['redirect_url']]);
    }
    
    
    public function updateCartByIPN(Request $requestData, $uuid){
        
    try{
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
    
        }
    
        } catch (\Exception $e) {
            
        }
}


    public function sendSmsMessage($body, $recipients, $sender, $smsId){
    
        $bearer = '5ed5a6f23fb215fa7c1a38ec12f58491';
        $taqnyt = new TaqnyatSms($bearer);
        
        try
        {
            $message = $taqnyt->sendMsg($body, $recipients, $sender, $smsId);
            return $message ? true : false;
        } 
        
        catch (\Exception $e) {
            return 'SMS Error: ' . $e->getMessage();
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

 
}