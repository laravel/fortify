<?php

namespace Laravel\Fortify\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Str;

class Password implements Rule
{
    /**
     * The minimum length of the password.
     *
     * @var int
     */
    protected $length = 8;

    /**
     * Indicates if the password must contain one uppercase character.
     *
     * @var numeric
     */
    protected $uppercaseCount = 0;

    /**
     * Indicates if the password must contain one numeric digit.
     *
     * @var numeric
     */
    protected $numericCount = 0;

    /**
     * Indicates if the password must contain one special character.
     *
     * @var numeric
     */
    protected $specialCharacterCount = 0;

    /**
     * The message that should be used when validation fails.
     *
     * @var string
     */
    protected $message;

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $value = is_scalar($value) ? (string) $value : '';

        if ($this->uppercaseCount !== (Str::length($value) - Str::length(preg_replace('/[A-Z]+/', '', $value)))) {
            return false;
        }

        if ($this->numericCount !== (Str::length($value) - Str::length(preg_replace('/[0-9]+/', '', $value)))) {
            return false;
        }

        if ($this->specialCharacterCount !== (Str::length($value) - Str::length(preg_replace('/[\W_]+/', '', $value)))) {
            return false;
        }

        return Str::length($value) >= $this->length;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if ($this->message) {
            return $this->message;
        }

        switch (true) {
            case $this->uppercaseCount
                && ! $this->numericCount
                && ! $this->specialCharacterCount:
                return __('The :attribute must be at least :length characters and contain at least :ucCount uppercase character.', [
                    'length' => $this->length,
                    'ucCount'=> $this->uppercaseCount,
                ]);

            case $this->numericCount
                && ! $this->uppercaseCount
                && ! $this->specialCharacterCount:
                return __('The :attribute must be at least :length characters and contain at least :numCount number.', [
                    'length' => $this->length,
                    'numCount'=> $this->numericCount,
                ]);

            case $this->specialCharacterCount
                && ! $this->uppercaseCount
                && ! $this->numericCount:
                return __('The :attribute must be at least :length characters and contain at least :specialCount special character.', [
                    'length' => $this->length,
                    'specialCount'=> $this->specialCharacterCount,
                ]);

            case $this->uppercaseCount
                && $this->numericCount
                && ! $this->specialCharacterCount:
                return __('The :attribute must be at least :length characters and contain at least :ucCount uppercase character and :numCount number.', [
                    'length' => $this->length,
                    'ucCount'=> $this->uppercaseCount,
                    'numCount'=> $this->numericCount,
                ]);

            case $this->uppercaseCount
                && $this->specialCharacterCount
                && ! $this->numericCount:
                return __('The :attribute must be at least :length characters and contain at least :ucCount uppercase character and :specialCount special character.', [
                    'length' => $this->length,
                    'ucCount'=> $this->uppercaseCount,
                    'specialCount'=> $this->specialCharacterCount,
                ]);

            case $this->uppercaseCount
                && $this->numericCount
                && $this->specialCharacterCount:
                return __('The :attribute must be at least :length characters and contain at least :ucCount uppercase character, :numCount number, and :specialCount special character.', [
                    'length' => $this->length,
                    'ucCount'=> $this->uppercaseCount,
                    'numCount'=> $this->numericCount,
                    'specialCount'=> $this->specialCharacterCount,
                ]);

            case $this->numericCount
                && $this->specialCharacterCount
                && ! $this->uppercaseCount:
                return __('The :attribute must be at least :length characters and contain at least :specialCount special character and :numCount number.', [
                    'length' => $this->length,
                    'specialCount'=> $this->specialCharacterCount,
                    'numCount'=> $this->numericCount,
                ]);

            default:
                return __('The :attribute must be at least :length characters.', [
                    'length' => $this->length,
                ]);
        }
    }

    /**
     * Set the minimum length of the password.
     *
     * @param  int  $length
     * @return $this
     */
    public function length(int $length)
    {
        $this->length = $length;

        return $this;
    }

    /**
     * Indicate that at least one uppercase character is required.
     *
     * @param positive-int $length
     * @return $this
     */
    public function requireUppercase(int $length = 1)
    {
        $this->uppercaseCount = abs($length);

        return $this;
    }

    /**
     * Indicate that at least one numeric digit is required.
     *
     * @param positive-int $length
     * @return $this
     */
    public function requireNumeric(int $length = 1)
    {
        $this->numericCount = abs($length);

        return $this;
    }

    /**
     * Indicate that at least one special character is required.
     *
     * @param positive-int $length
     * @return $this
     */
    public function requireSpecialCharacter(int $length = 1)
    {
        $this->specialCharacterCount = abs($length);

        return $this;
    }

    /**
     * Set the message that should be used when the rule fails.
     *
     * @param  string  $message
     * @return $this
     */
    public function withMessage(string $message)
    {
        $this->message = $message;

        return $this;
    }
}
