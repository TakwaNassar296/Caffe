<?php

namespace App\Http\Requests\Api\UserPayment;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserCardRequest extends FormRequest
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
           'name'        => 'required|string|max:255',
            'card_number' => 'required|string|max:20',
            'cvv'         => 'required|string|digits:3',
            'expire_date' => 'required|date_format:m/y',
        ];
    }
}
