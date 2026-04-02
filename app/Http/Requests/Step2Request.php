<?php

namespace App\Http\Requests;

use App\Rules\BirthDate;
use App\Rules\CheckAge;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class Step2Request extends FormRequest
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
     * @return array
     */
    public function rules(): array
    {
        return [
            // Owner
            'property_owner_id_num' => 'required|max:10',
             
           
            
            'property_owner_dob_hijri' =>  
                ['required', new CheckAge],
             
            'property_owner_mobile' => [function ($attribute, $value, $fail) {
                if ($this->property_owner_is_deceased == 0 && empty($value)) {
                    $fail('يجب أضافة رقم جوال المالك');
                }
            },
            'max:10'
        ],

        'property_owner_iban' => [
        function ($attribute, $value, $fail) {
             
            if ($this->property_owner_is_deceased == 0 && empty($value)) {
                $fail('يجب إضافة رقم ايبان المالك');
            }
        },
        'nullable', 
        'max:24',   
        function ($attribute, $value, $fail) {
           
            if (!preg_match('/^[A-Z]{2}\d{22}$/', $value)) {
                $fail('رقم ايبان المالك غير صحيح');  
            }
        },
    ],

            // Agent owner
            // 'add_legal_agent_of_owner' => 'required_if:property_owner_is_deceased,1',
            // 'id_num_of_property_owner_agent' => function ($attribute, $value, $fail) {
            //     if ($this->add_legal_agent_of_owner == 1 && empty($value)) {
            //         $fail('يجب أضافه رقم هويه وكيل المالك');
            //     }
            // },
          
            // 'dob_hijri_of_property_owner_agent' => [
                 
            //     ['required', new CheckAge],
            // ],
    
            // Tenant
            'tenant_entity' => 'required',
            'tenant_id_num' => 'required',
            
            'tenant_dob_hijri' => [
                'required', 
                'date',
                'before:' . now()->subYears(18),
            ],
    
            // Agent tenant
            'add_legal_agent_of_tenant' => 'required|in:1,0',
           
            'dob_hijri_of_property_tenant_agent' => [
                'date',
                'before:' . now()->subYears(18),
            ],

            'dob_hijri_of_property_tenant_agent' => function ($attribute, $value, $fail) {
                if ($this->add_legal_agent_of_tenant == 1 && empty($value)) {
                    $fail('يجب إدخال تاريخ ميلاد  لمستأجر العقار.');
                }
            },

          

            'agency_number_in_instrument_of_property_tenant' => function ($attribute, $value, $fail) {
                if ($this->add_legal_agent_of_tenant == 1 && empty($value)) {
                    $fail('يجب إدخال رقم الوكالة في الصك لمستأجر العقار.');
                }
            },
            'mobile_of_property_tenant_agent' => function ($attribute, $value, $fail) {
                if ($this->add_legal_agent_of_tenant == 1 && empty($value)) {
                    $fail('يجب إدخال رقم الجوال لوكيل مستأجر العقار.');
                }
            },
        ];
    }
    
    public function messages()
    {
        return [
            // Owner
            'property_owner_id_num.required' => 'رقم الهوية مطلوب',
            'property_owner_id_num.max' => 'رقم الهوية يجب ألا يزيد عن 10 أحرف',
              'property_owner_dob_hijri.required' => 'تاريخ ميلاد المالك  مطلوب',
            'property_owner_dob_hijri.before' => 'يجب أن يكون عمر المالك أكبر من 18 عامًا',
             'property_owner_mobile.required'=>'رقم هاتف المالك مطلوب', 
             'property_owner_mobile.max'=>'رقم هاتف المالك لا يزيد عن 10 ارقام', 
             'property_owner_iban.max'=>' رقم الايبان لا يزيد عن 24 رقم',
            // Agent Owner
            'add_legal_agent_of_owner.required_if' => 'يجب تعيين وكيل قانوني إذا كان المالك متوفياً',
             'dob_hijri_of_property_owner_agent.before' => 'يجب أن يكون عمر وكيل المالك أكبر من 18 عامًا',
    
            // Tenant
            'tenant_entity.required' => 'يجب اختيار صفة المستأجر',
            'tenant_id_num.required' => 'رقم هوية المستأجر مطلوب',
              'tenant_dob_hijri.required' => 'تاريخ ميلاد المستأجر  مطلوب',
            'tenant_dob_hijri.before' => 'يجب أن يكون عمر المستأجر أكبر من 18 عامًا',
    
            // Agent Tenant
            'add_legal_agent_of_tenant.required' => 'يجب تحديد ما إذا كنت ترغب في إضافة وكيل للمستأجر',
            'add_legal_agent_of_tenant.in' => 'القيمة المدخلة لـ "إضافة وكيل للمستأجر" غير صحيحة',
             'dob_hijri_of_property_tenant_agent.before' => 'يجب أن يكون عمر وكيل المستأجر أكبر من 18 عامًا',
        //    'dob_hijri_of_property_tenant_agent.required'=>'',
            // Custom error messages for specific validations
            'property_owner_mobile.required_if' => 'رقم الجوال مطلوب عندما يكون المالك حياً',
            'property_owner_iban.required_if' => 'رقم الآيبان مطلوب عندما يكون المالك حياً',
            'id_num_of_property_owner_agent.required_if' => 'يجب إدخال رقم الهوية لوكيل مالك العقار.',
            'agency_number_in_instrument_of_property_tenant.required_if' => 'يجب إدخال رقم الوكالة في الصك لمستأجر العقار.',
            'mobile_of_property_tenant_agent.required_if' => 'يجب إدخال رقم الجوال لوكيل مستأجر العقار.',

            //date vaild

             'dob_hijri_of_property_tenant_agent.date' => 'حقل تاريخ الميلاد  للوكيل الشرعي لمستأجر العقار يجب أن يكون تاريخاً صالحاً.',
            'tenant_dob_hijri.date' => 'حقل تاريخ   لمستأجر العقار يجب أن يكون تاريخاً صالحاً.',
             'dob_hijri_of_property_owner_agent.date' => 'حقل تاريخ الميلاد  للوكيل الشرعي لمستأجر العقار يجب أن يكون تاريخاً صالحاً.',
             'property_owner_dob_hijri.date' => 'حقل تاريخ الميلاد  لمالك العقار يجب أن يكون تاريخاً صالحاً.',
 
        ];
    }
    
}