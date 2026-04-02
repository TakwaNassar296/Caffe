<?php

namespace App\Http\Requests\Drive\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class RegisterRequest extends FormRequest
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
            'profile_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'first_name' => 'required|string|max:255|min:3',
            'last_name' => 'required|string|max:255|min:3',
            'phone_number' => 'required|string|max:20',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
            'id_number' => 'required|string|max:50',
            'date_of_birth' => 'required|date',
            'nationality' => 'required|string|max:100',

            'vehicle_registration_document' => 'required|mimes:jpeg,png,jpg,pdf|max:2048',
            'vehicle_insurance_document' => 'nullable|mimes:jpeg,png,jpg,pdf|max:2048',
            'type_of_vehicle' => 'required|string|max:50',
            'vehicle_model' => 'required|string|max:100',
            'year_of_manufacture' => 'required|string|max:4',
            'license_plate_number' => 'required|string|max:50',

            'driving_license_photo' => 'required|mimes:jpeg,png,jpg,pdf|max:2048',
            'license_issue_date' => 'required|date',
            'license_expiry_date' => 'required|date|after:license_issue_date',
            'previous_experience' => 'required|boolean',
            'experience' => 'nullable|string',

            'city' => 'required|string|max:100',
            'district_area' => 'required|string|max:100',
            'have_gps' => 'required|boolean',
            'notes' => 'nullable|string',
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
