<?php

namespace App\Http\Requests\Branch;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreAwardPointsRequest extends FormRequest
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
            'employee_id' => 'required|exists:admins,id',
            'point_amount' => 'required|numeric|min:0.01',
            'type_reason' => 'required|in:punctuality_opening_on_time,extra_shift,customer_compliment,exceptional_performance,cleanliness_hygiene,other',
            'other_reason' => 'required_if:type_reason,other|nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'employee_id.required' => 'Employee is required.',
            'employee_id.exists' => 'Selected employee does not exist.',
            'point_amount.required' => 'Points amount is required.',
            'point_amount.numeric' => 'Points amount must be a number.',
            'point_amount.min' => 'Points amount must be at least 0.01.',
            'type_reason.required' => 'Reason is required.',
            'type_reason.in' => 'Invalid reason selected.',
            'other_reason.required_if' => 'Other reason is required when reason is "Other".',
            'notes.max' => 'Notes cannot exceed 1000 characters.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => false,
            'message' => 'Validation failed',
            'data' => $validator->errors(),
        ], 422));
    }
}
