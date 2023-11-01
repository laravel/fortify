<?php

namespace Laravel\Fortify\Rules;

use Illuminate\Contracts\Validation\Rule;

class HasSpecialCharacter implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param $attribute
     * @param $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return preg_match('/[\W_]/', $value);
    }

    /**
     * Get the validation error message.
     *
     * @return array|string
     */
    public function message()
    {
        return __('fortify::validation.password.special_character');
    }
}
