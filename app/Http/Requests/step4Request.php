<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\StartContractDate;
class Step4Request extends FormRequest
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
            'contract_starting_date' => 'required',
            'contract_period_id' => 'required',
            'payment_type_id'=>'required',
        ];

    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'contract_starting_date.required' => 'تاريخ بداية العقد مطلوب',
            // 'contract_starting_date.valid_contract_date' => trans('website.valid_contract_date'),
            'payment_type_id.required' => 'طريقة الدفعات مطلوبة',
            'contract_period_id.required' => 'مدة العقد بالسنة مطلوبة',
        ];
    }
}
