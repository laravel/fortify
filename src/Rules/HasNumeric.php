<?php

namespace Laravel\Fortify\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class HasNumeric implements ValidationRule
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
        if (! preg_match('/[0-9]/', $value)) {
            $fail(__('fortify::validation.password.numeric'));
        }
    }
}
