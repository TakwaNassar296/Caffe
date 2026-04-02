<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Step1Request extends FormRequest
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
           
            'contract_ownership' => 'required',
            'instrument_type' => 'required|in:electronic,old_handwritten,strong_argument',
            'instrument_number' => ['required_if:instrument_type,electronic'],
            'instrument_history' => ['required_if:instrument_type,electronic'],
            'property_type_id' => 'required',
            'property_usages_id' => 'required',
             'date_first_registration' => ['required_if:instrument_type,strong_argument'],
            'real_estate_registry_number' => ['required_if:instrument_type,strong_argument'],
            'property_owner_is_deceased' => 'required',
            'property_city_id' => 'required ',
            'property_place_id' => 'required ',
            'building_number' => 'required|max:4',
            'postal_code' => 'required|max:5',
            'neighborhood'=>'required',
            'extra_figure' => 'required|max:4',
            'street'=>'required',
             'number_of_floors'=>'required',
 
            ] ;
    }
    public function messages(): array
    {
        return [
             
             'instrument_number.required_if' =>  trans('validation.required_if_electronic'),
            'instrument_history.required_if' => trans('validation.required_if_electronic'),
            'property_type_id.required' =>  trans('validation.required'),
             'strong_argument_photo.required_if' => trans('validation.required_if_strong_argument'),
            'property_owner_is_deceased.required_if' => trans('validation.required_if'),
            'property_owner_is_deceased.required' => trans('validation.required'),
             'property_city_id.required' => trans('validation.required'),
              'building_number.required' => trans('validation.required'),
            'postal_code.required' => trans('validation.required'),
            'extra_figure.required' => trans('validation.required'),
             'postal_code.max'=>trans('validation.max_digits'),
            'building_number.max'=>trans('validation.max_digits'),
            'extra_figure.max'=>trans('validation.max_digits'),
            'neighborhood'=>trans('validation.required'),
            'number_of_floors'=>trans('validation.required_number_of_floors'),
            'date_first_registration'=>trans('validation.date_first_registration'),
            'real_estate_registry_number'=>trans('validation.real_estate_registry_number'),
        ];
    }
}