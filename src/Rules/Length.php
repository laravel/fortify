<?php

namespace Laravel\Fortify\Rules;

use Illuminate\Contracts\Validation\Rule;

class Length implements Rule
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
     * Determine if the validation rule passes.
     *
     * @param $attribute
     * @param $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return mb_strlen($value) >= $this->length;
    }

    /**
     * Get the validation error message.
     *
     * @return array|string
     */
    public function message()
    {
        return __('fortify::validation.password.length', ['length' => $this->length]);
    }
}
