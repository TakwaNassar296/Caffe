<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Seo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SeoController extends Controller
{
    public function login_page()
    {
        return view('panel.pages.auth.login-seo');
    }

    public function login_check(Request $request)
    {
        $rules = [
            'email' => 'required|email',
            'password' => 'required',
        ];
    
       
        $this->validate($request, $rules);
    
        $credentials = ['email' => $request->email, 'password' => $request->password];
 
     
        if (Auth::guard('seo')->attempt($credentials)) {
            Session::flash('success', 'تم تسجيل الدخول بنجاح');
            
             $request->session()->regenerate();
          
            return redirect()->route('seo.home');  
        }
    
        // Redirect back with email and error message
        return back()->withInput($request->only('email'))->with('error', 'يرجى التأكد من البريد الالكتروني و كلمة المرور');
    }
    
}
