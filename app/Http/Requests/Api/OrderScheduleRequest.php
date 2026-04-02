<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class OrderScheduleRequest extends FormRequest
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
            'order_type'        => 'required|string|in:delivery,pick_up',
            'branch_id'         => 'required|exists:branches,id',
            'schedual_date'     => 'required|date|after:now',
            'products'          => 'required|array|min:1',
            'products.*.id'      => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.option_values' => 'nullable|array',
            'products.*.option_values.*' => 'integer|exists:product_values,id',
            'user_location_id'  => 'nullable|exists:user_locations,id',
            'user_payment_id'   => 'nullable|exists:user_payments,id',
            'pay_with'          => 'required|in:money,points',

            'payment_method'    => 'required|string',
        ];
    }


 protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => false,
            'message' => $validator->errors()->first(),
            'data' => [],
        ], 422));
    }
}
