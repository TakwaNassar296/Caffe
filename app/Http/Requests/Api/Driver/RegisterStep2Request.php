<?php

namespace App\Http\Requests\Api\Driver;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterStep2Request extends FormRequest
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
            'email' => 'required|email|exists:drivers,email',
            'vehicle_registration_document' => 'required|mimes:jpeg,png,jpg,pdf|max:2048',
            'vehicle_insurance_document' => 'nullable|mimes:jpeg,png,jpg,pdf|max:2048',
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'vehicle_model' => 'required|string|max:100',
            'year_of_manufacture' => 'required|string|max:4',
            'license_plate_number' => 'required|string|max:50',
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
