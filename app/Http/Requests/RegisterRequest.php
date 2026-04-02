<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'fname'=>'required|string|max:255',
            'email' => 'unique:users',
            'mobile' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
 
        ];
    }

    public function messages()
    {
        return [
            'fname.required' =>'الاسم مطلوب بالكامل ',
            'email.required' =>'البريد الالكتروني مطلوب ',
            'email.unique' =>'البريد الالكتروني مرتبط بحساب اخر',
            'mobile.required' => 'رقم الجوال مطلوب',
            'mobile.unique' => 'رقم الجوال مرتبط بحساب اخر',
            'password.required' => 'كلمة المرور مطلوبه',
            'password.min' => 'يجب الا تقل كلمة السر عن 8 ارقام او حروف',
            'password.confirmed'=>'يجب عليك تاكيد المرور',

        ];
    }
}