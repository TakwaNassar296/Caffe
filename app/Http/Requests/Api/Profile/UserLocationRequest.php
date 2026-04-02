<?php

namespace App\Http\Requests\Api\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserLocationRequest extends FormRequest
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
            'name_address'       => 'required|string|max:255|min:3',
            'building_number'    => 'required|string|max:255',
            'floor'              => 'required|string|max:255',
            'apartment'          => 'required|string|max:255|',
            'address_description'=> 'required|string|max:1000|min:3',
            'address_title'      => 'required|string|max:255|min:3',
            'latitude'           => 'required|numeric|between:-90,90',
            'longitude'          => 'required|numeric|between:-180,180',
            'first_name'         => 'required|string|max:255|min:3',
            'last_name'          => 'required|string|max:255|min:3',
            'phone_number'       => 'required|string|max:20|min:8',

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
