<?php

namespace App\Http\Requests\Api\Driver;

use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class RegisterStep1Request extends FormRequest
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
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'first_name' => 'required|string|max:255|min:3',
            'last_name' => 'required|string|max:255|min:3',
            'country_key' => 'required|string|max:6',
            'phone_number' => 'required|string|max:20|unique:drivers,phone_number|min:4',
            'email' => 'required|email|unique:drivers,email',
            'password' => 'required|string|min:8|confirmed',
            'id_number' => 'required|string|max:50',
            'date_of_birth' => 'required|date',
            'nationality' => 'required|string|max:100',
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
