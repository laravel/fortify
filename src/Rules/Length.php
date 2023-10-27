<?php

namespace Laravel\Fortify\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Length implements ValidationRule
{
    /**
     * The length required.
     *
     * @var int
     */
    protected $length;

    /**
     * Length validation rule constructor.
     *
     * @param  int  $length
     */
    public function __construct(int $length)
    {
        $this->length = $length;
    }

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
        if (mb_strlen($value) < $this->length) {
            $fail(__('fortify::validation.password.length', ['length' => $this->length]));
        }
    }
}
