<?php

namespace App\Rules;

use Carbon\Carbon;
use Carbon\Traits\Date;
use Illuminate\Contracts\Validation\Rule;

class CheckAge implements Rule
{
    public function passes($attribute, $value)
    {
        // Convert the date of birth to a Carbon instance
        $dob = Carbon::parse($value);

        // Calculate the age
        $age = $dob->age;

        // Check if the age is at least 18
        return $age >= 18;
    }


    public function message()
    {
        return 'يجب أن يكون تاريخ الميلاد صالحًا ويجب أن يكون الشخص بالغًا على الأقل 18 عامًا.';
    }
 
}
