<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Day implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $day = 0;
        if (is_numeric($value)) {
            $day = intval($value);
        }
        return $day >= 1 && $day <= 31;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.custom.date');
    }
}
