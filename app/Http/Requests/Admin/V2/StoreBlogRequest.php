<?php

namespace App\Http\Requests\Admin\V2;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBlogRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'status' => 'required|in:published,draft,scheduled',
            'publish_at' => [
                'nullable',
                'date',
                Rule::requiredIf($this->input('status') === 'scheduled'),
                function ($attribute, $value, $fail) {
                    if ($this->input('status') === 'scheduled' && $value && Carbon::parse($value)->isPast()) {
                        $fail('The publish_at must be in the future for scheduled blogs.');
                    }
                },
            ],
            'is_active' => 'nullable|boolean',
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
            'title.required' => trans('validation.required', ['attribute' => 'title']),
            'description.required' => trans('validation.required', ['attribute' => 'description']),
            'status.required' => trans('validation.required', ['attribute' => 'status']),
            'status.in' => trans('validation.in', ['attribute' => 'status']),
            'publish_at.required' => trans('validation.required', ['attribute' => 'publish_at']),
            'publish_at.date' => trans('validation.date', ['attribute' => 'publish_at']),
            'image.image' => trans('validation.image', ['attribute' => 'image']),
            'image.mimes' => trans('validation.mimes', ['attribute' => 'image', 'values' => 'jpeg,png,jpg,gif,svg']),
            'image.max' => trans('validation.max.file', ['attribute' => 'image', 'max' => '2048']),
        ];
    }
}
