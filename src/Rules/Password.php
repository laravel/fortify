<?php

namespace Laravel\Fortify\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Password implements ValidationRule
{
    /**
     * The validation rules to apply.
     *
     * @var \Illuminate\Contracts\Validation\ValidationRule[]
     */
    protected $rules = [];

    /**
     * Initialize the validation rule and set the default password length.
     *
     * @param  int  $length
     */
    public function __construct(int $length = 8)
    {
        $this->length($length);
    }

    /**
     * Set the minimum length of the password.
     *
     * @param  int  $length
     * @return $this
     */
    public function length(int $length): self
    {
        return $this->upsertRule(new Length($length));
    }

    /**
     * Indicate that at least one numeric character is required.
     *
     * @return $this
     */
    public function requireNumeric(): self
    {
        return $this->upsertRule(new HasNumeric());
    }

    /**
     * Indicate that at least one special character is required.
     *
     * @return $this
     */
    public function requireSpecialCharacter(): self
    {
        return $this->upsertRule(new HasSpecialCharacter());
    }

    /**
     * Indicate that at least one uppercase character is required.
     *
     * @return $this
     */
    public function requireUppercase(): self
    {
        return $this->upsertRule(new HasUppercase());
    }

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        foreach ($this->rules as $rule) {
            $rule->validate($attribute, $value, $fail);
        }
    }

    /**
     * Update or insert a rule in the validation rules to apply.
     *
     * @param  \Illuminate\Contracts\Validation\ValidationRule  $rule
     * @return $this
     */
    private function upsertRule(ValidationRule $rule): self
    {
        $this->rules[$rule::class] = $rule;

        return $this;
    }
}
