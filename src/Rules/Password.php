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
     * @var bool
     */
    protected $requireUppercase = false;

    /**
     * minimum number of uppercase character in password.
     *
     * @var int
     */
    protected $atLestUppercaseCount = 1;

    /**
     * Indicates if the password must contain one numeric digit.
     *
     * @var bool
     */
    protected $requireNumeric = false;

    /**
     * minimum number of numeric digit in password.
     *
     * @var int
     */
    protected $atLestNumericCount = 1;

    /**
     * Indicates if the password must contain one special character.
     *
     * @var bool
     */
    protected $requireSpecialCharacter = false;

    /**
     * minimum number of special character in password.
     *
     * @var int
     */
    protected $atLestSpecialCharacterCount = 1;

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

        if ($this->requireUppercase && preg_match_all('/[\p{Lu}]/', $value) < $this->atLestUppercaseCount) {
            return false;
        }

        if ($this->requireNumeric && preg_match_all('/[0-9]/', $value) < $this->atLestNumericCount) {
            return false;
        }

        if ($this->requireSpecialCharacter && preg_match_all('/[\W_]/', $value) < $this->atLestSpecialCharacterCount) {
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
            case $this->requireUppercase
                && ! $this->requireNumeric
                && ! $this->requireSpecialCharacter:
                return __('The :attribute must be at least :length characters and contain at least :atLeastUpperCaseCount uppercase character.', [
                    'length' => $this->length,
                    'atLeastUpperCaseCount' => $this->atLestUppercaseCount,
                ]);

            case $this->requireNumeric
                && ! $this->requireUppercase
                && ! $this->requireSpecialCharacter:
                return __('The :attribute must be at least :length characters and contain at least :atLestNumericCount number.', [
                    'length' => $this->length,
                    'atLestNumericCount' => $this->atLestNumericCount,
                ]);

            case $this->requireSpecialCharacter
                && ! $this->requireUppercase
                && ! $this->requireNumeric:
                return __('The :attribute must be at least :length characters and contain at least :atLestSpecialCharacter special character.', [
                    'length' => $this->length,
                    'atLestSpecialCharacter' => $this->atLestSpecialCharacterCount,
                ]);

            case $this->requireUppercase
                && $this->requireNumeric
                && ! $this->requireSpecialCharacter:
                return __('The :attribute must be at least :length characters and contain at least :atLeastUpperCaseCount uppercase character and :atLestNumericCount number.', [
                    'length' => $this->length,
                    'atLeastUpperCaseCount' => $this->atLestUppercaseCount,
                    'atLestNumericCount' => $this->atLestNumericCount,
                ]);

            case $this->requireUppercase
                && $this->requireSpecialCharacter
                && ! $this->requireNumeric:
                return __('The :attribute must be at least :length characters and contain at least :atLeastUpperCaseCount uppercase character and :atLestSpecialCharacter special character.', [
                    'length' => $this->length,
                    'atLeastUpperCaseCount' => $this->atLestUppercaseCount,
                    'atLestSpecialCharacter' => $this->atLestSpecialCharacterCount,
                ]);

            case $this->requireUppercase
                && $this->requireNumeric
                && $this->requireSpecialCharacter:
                return __('The :attribute must be at least :length characters and contain at least :atLeastUpperCaseCount uppercase character, :atLestNumericCount number, and :atLestSpecialCharacter special character.', [
                    'length' => $this->length,
                    'atLeastUpperCaseCount' => $this->atLestUppercaseCount,
                    'atLestNumericCount' => $this->atLestNumericCount,
                    'atLestSpecialCharacter' => $this->atLestSpecialCharacterCount,
                ]);

            case $this->requireNumeric
                && $this->requireSpecialCharacter
                && ! $this->requireUppercase:
                return __('The :attribute must be at least :length characters and contain at least :atLestSpecialCharacter special character and :atLestNumericCount number.', [
                    'length' => $this->length,
                    'atLestNumericCount' => $this->atLestNumericCount,
                    'atLestSpecialCharacter' => $this->atLestSpecialCharacterCount,
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
     * @return $this
     */
    public function requireUppercase(int $atLestCount = 1)
    {
        $this->requireUppercase = true;
        $this->atLestUppercaseCount = $atLestCount;

        return $this;
    }

    /**
     * Indicate that at least one numeric digit is required.
     *
     * @return $this
     */
    public function requireNumeric(int $atLestCount = 1)
    {
        $this->requireNumeric = true;
        $this->atLestNumericCount = $atLestCount;

        return $this;
    }

    /**
     * Indicate that at least one special character is required.
     *
     * @return $this
     */
    public function requireSpecialCharacter(int $atLestCount = 1)
    {
        $this->requireSpecialCharacter = true;
        $this->atLestSpecialCharacterCount = $atLestCount;

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
