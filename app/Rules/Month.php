<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Month implements Rule
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
        $month = -1;
        if (is_numeric($value)) {
            $month = intval($value);
        }
        return $month >= 0 && $month <= 12;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.custom.month');
    }
}
