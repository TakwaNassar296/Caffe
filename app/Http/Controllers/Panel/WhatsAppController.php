<?php

namespace App\Http\Controllers\Panel;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
 class WhatsAppController extends Controller
{
    public function sendWhatsAppMessage(Request $request)
    {
        $apiKey = 'b18583cdc94eb282bb7fe0f54e3b1f7b_O5fyOhjeFcwNSREvfLjwRHGSI7zZ0oibW6v79dMJ';
        $apiUrl = 'https://go-wloop.net/api/v1/message/send';

        $phone = $request->input('phone');
        $message = $request->input('message');

        $response = Http::withToken($apiKey)->post($apiUrl, [
            'phone' =>$phone,
            'body' => $message,
        ]);


        if ($response->successful()) {
            return redirect()->back()->with(['success' => 'تم الارسال بنجاح']);
        } else {
            return redirect()->back()->with(['error' => 'فشل الارسال ']);
        }
    }


}