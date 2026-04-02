<?php

namespace App\Http\Requests\Api\Profile;

use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class UpdateProfileRequest extends FormRequest
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
            'first_name' => 'required|required|string|max:255|min:3',
            'last_name'  => 'required|required|string|max:255|min:3',
            'country_key' => 'required|string|max:6',
            'phone_number' => 'required|string|max:20|min:4|unique:users,phone_number,' . auth('user')->id(),
            'image'      => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'new_password' => 'nullable|min:6|max:255|string',
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
