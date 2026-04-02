<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function login_page()
    {

        return view('panel.pages.auth.login');
    }

    public function login_check(Request $request)
    {
        $rules = [
            'email' => 'required|email',
            'password' => 'required',
        ];

        $this->validate($request, $rules);

        $credentials = ['email' => $request->email, 'password' => $request->password];

        if (Auth::guard("admin")->attempt($credentials)) {
         
             Session::flash('success',  'تمت تسجيل الدخول بنجاح');

            $request->session()->regenerate();

            return redirect()->route('admin.home');
        }

        return back()->onlyInput('email')->withFlashMessage('يرجى التأكد من البريدالالكتروني و كلمة المرور');
    }

    public function profile_page()
    {
        return view('panel.pages.auth.profile');
    }

    public function update_profile(Request $request)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:admins,email,' . auth()->id(),
            'mobile' => 'required',
            'photo' => 'mimes:png,jpg,jpeg',
        ];
        $this->validate($request, $rules);

        $admin = Admin::findOrFail(auth()->id());

        try {

            $data = $request->only('name', 'email', 'mobile');

            if ($request->hasFile('photo')) {
                $data['photo'] = fileUploader($request->file('photo'), 'admins');
            }

            $admin->update($data);

            return response()->json([
                "success" => true,
                "message" => 'تمت العملية بنجاح',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
            ]);
        }
    }

    public function update_password(Request $request)
    {
        $rules = [
            'old_password' => 'required',
            'new_password' => 'required|confirmed',
        ];
        $this->validate($request, $rules);

        $admin = Admin::findOrFail(auth()->id());

        if (Hash::check($request->old_password, $admin->password)) {
            $admin->update([
                'password' => bcrypt($request->new_password)
            ]);

            Session::flash('success', 'تمت العملية بنجاح');
            return redirect()->back();
        }

        Session::flash('error', 'كلمة المرور خاطئة');
        return redirect()->back();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        Session::flash('success', 'تمت تسجيل الخروج بنجاح');
        return redirect()->route('admin.login');
    }

}
