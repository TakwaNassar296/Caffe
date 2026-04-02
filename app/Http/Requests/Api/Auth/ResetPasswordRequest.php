<?php

namespace App\Http\Requests\Api\Auth;
use App\Support\PhoneNumber;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ResetPasswordRequest extends FormRequest
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
            'country_key' => 'nullable|string|max:6',
            'phone_number' => 'required|string|max:20|min:4',
            'otp' => 'required|string|max:4',
            'new_password' => 'required|min:8|max:255|confirmed',
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
