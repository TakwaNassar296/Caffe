<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class step3Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
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
            'unit_type_id'=>'required',
            'unit_number'=>'required',
            'floor_number'=>'required',
            'unit_area'=>'required',
             
        ];
    }
    public function messages(): array
    {
        return [
            'unit_type_id.required' => trans('validation.required'),
            'unit_number.required' => trans('validation.required'),
             'unit_usage.required' => trans('validation.required'),
            'unit_area'=>trans('validation.required'),
        ];
    }
}