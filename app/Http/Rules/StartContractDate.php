<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class StartContractDate implements Rule
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
         $date = Carbon::createFromFormat('Y-m-d', $value);
        
         return $date->gte(now()->subDays(280));
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '  :attribute يجب الا يكون مر عليه اكثر من 280 يوما.';
    }
}
