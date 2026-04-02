<?php

namespace App\Http\Requests\Api\Order;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CheckoutRequest extends FormRequest
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
            'coupon_code' => 'nullable|string|exists:coupons,code',
            'user_location_id' => 'nullable|exists:user_locations,id|required_if:type,delivery',
            // 'user_payment_id' => 'nullable|exists:user_payments,id',
            'payment_method' => 'nullable|string',
            'type' => 'required|in:delivery,pick_up',
            'pay_with' => 'required|in:money,points',
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
