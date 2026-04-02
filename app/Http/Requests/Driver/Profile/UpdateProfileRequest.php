<?php

namespace App\Http\Requests\Driver\Profile;

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
            'phone_number'      => 'required|string|max:20|min:8|unique:drivers,phone_number,' . auth('driver')->id(),
            'image'      => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'new_password' => 'nullable|min:6|max:255|string',
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
