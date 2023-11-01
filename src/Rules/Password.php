<?php

namespace Laravel\Fortify\Rules;

use Illuminate\Contracts\Validation\Rule;

class Password implements Rule
{
    /**
     * The validation rule error message.
     *
     * @var string
     */
    protected $error;

    /**
     * The validation rules to apply.
     *
     * @var \Illuminate\Contracts\Validation\Rule[]
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
    public function requireNumeric()
    {
        return $this->upsertRule(new HasNumeric());
    }

    /**
     * Indicate that at least one special character is required.
     *
     * @return $this
     */
    public function requireSpecialCharacter()
    {
        return $this->upsertRule(new HasSpecialCharacter());
    }

    /**
     * Indicate that at least one uppercase character is required.
     *
     * @return $this
     */
    public function requireUppercase()
    {
        return $this->upsertRule(new HasUppercase());
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param $attribute
     * @param $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        foreach ($this->rules as $rule) {
            if (! $rule->passes($attribute, $value)) {
                // If the current validation rule fails, capture the error message
                // and return false to break the loop.
                $this->error = $rule->message();
                
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->error;
    }

    /**
     * Update or insert a rule in the validation rules to apply.
     *
     * @param  \Illuminate\Contracts\Validation\Rule  $rule
     * @return $this
     */
    private function upsertRule(Rule $rule)
    {
        $key = get_class($rule);

        $this->rules[$key] = $rule;

        return $this;
    }
}
