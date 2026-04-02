<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z]).{8,}$/|confirmed',
 
        ];
    }

    public function messages()
    {
        return [
             
            'password.required' => trans('validation.required'),
            'password.min' => trans('validation.min.numeric'),
            'password.regex'=>trans('validation.password.mixed'),
            'password.confirmed'=>trans('validation.confirmed'),

        ];
    }
}