<?php

namespace Laravel\Fortify\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class HasUppercase implements ValidationRule
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
        if (! preg_match('/[A-Z]/', $value)) {
            $fail(__('fortify::validation.password.uppercase'));
        }
    }
}
