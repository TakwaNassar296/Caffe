<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function settings()
    {

        $setting = Setting::where('id', '1')->first();
        return view('panel.pages.settings.index', compact('setting'));
    }

    public function settingsUpdate(Request $request)
    {

        $rules = [
            '*' => 'required',
            'housing_tax' => 'integer',
            'commercial_tax' => 'integer',
            'application_fees' => 'integer'
        ];

        $messages = [
            '*.required' => 'هذا الحقل مطلوب'
        ];

        $this->validate($request, $rules, $messages);

        try {
            $date = $request->only('whatsapp', 'instagram', 'twitter', 
            'text_message_user','text_message_admin','is_open',
            'snapchat','open_payment','version', 'housing_tax','mobile_contract',
            'facebook','linkedIn','tiktok', 'commercial_tax', 'application_fees','working_hours');

            $item = Setting::updateOrCreate(['id' => '1'], $date);

            return response()->json([
                "success" => true,
                "data" => $item,
                "message" => "تمت العملية بنجاح",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
            ]);
        }
    }
}
