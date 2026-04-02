<?php

namespace App\Http\Requests\Api\Auth;

use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
class LoginRequest extends FormRequest
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
            'country_key' => 'required|string|max:6',
            'phone_number' => 'required|string|max:20|min:4',
            'password' => 'required|string|min:8|max:255',
            'remember_me' => 'boolean|nullable',
            'fcm_token' => 'nullable|string|max:255',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone_number' => PhoneNumber::normalize(
                (string) $this->input('phone_number'),
                (string) $this->input('country_key'),
            ),
        ]);
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
