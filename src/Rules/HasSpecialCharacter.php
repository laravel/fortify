<?php

namespace Laravel\Fortify\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class HasSpecialCharacter implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  Closure  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! preg_match('/[\W_]/', $value)) {
            $fail(__('fortify::validation.password.special_character'));
        }
    }
}
