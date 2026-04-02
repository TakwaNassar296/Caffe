<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Carbon\Carbon;

class BirthDate implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
         if (empty($value)) {
            return false;
        }

         $dateOfBirth = Carbon::createFromFormat('Y-m-d', $value);

         return $dateOfBirth && $dateOfBirth->lte(now()->subYears(18));
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '  :attribute يجب ان لا يكون عمره اقل من 18 عام .';
    }
}
