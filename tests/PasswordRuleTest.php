<?php

namespace Laravel\Fortify\Tests;

use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Rules\Password;

class PasswordRuleTest extends OrchestraTestCase
{
    public function test_password_rule()
    {
        $rule = new Password;

        $this->assertPasses($rule, 'password');
        $this->assertPasses($rule, '234234234');
        $this->assertFails($rule, ['foo' => 'bar'], 'must be at least 8 characters.');
        $this->assertFails($rule, 'secret', 'must be at least 8 characters.');

        $rule->length(10);

        $this->assertFails($rule, 'password', 'must be at least 10 characters');
        $this->assertPasses($rule, 'password11');

        $rule->length(8)->requireUppercase();

        $this->assertFails($rule, 'password', 'characters and contain at least one uppercase character');
        $this->assertPasses($rule, 'Password');

        $rule->length(8)->requireNumeric();

        $this->assertFails($rule, 'Password', 'characters and contain at least one uppercase character and one number');
        $this->assertFails($rule, 'password1', 'characters and contain at least one uppercase character and one number');
        $this->assertPasses($rule, 'Password1');
    }

    public function test_password_rule_can_require_special_characters()
    {
        $rule = new Password;

        $rule->length(8)->requireSpecialCharacter();

        $this->assertPasses($rule, 'password!');
        $this->assertFails($rule, 'password', 'must be at least 8 characters and contain at least one special character');
    }

    public function test_password_rule_can_require_numeric_and_special_characters()
    {
        $rule = new Password;

        $rule->length(10)->requireNumeric()->requireSpecialCharacter();

        $this->assertPasses($rule, 'password5%');
        $this->assertFails($rule, 'my-password', 'must be at least 10 characters and contain at least one special character and one number');
    }

    private function assertFails(Password $rule, $value, $message): void
    {
        $this->assertThrows(function () use ($rule, $value) {
            validator(['password' => $value], ['password' => [$rule]])->validate();
        }, ValidationException::class, $message);
    }

    private function assertPasses(Password $rule, $value): void
    {
        try {
            validator(['password' => $value], ['password' => [$rule]])->validate();

            $thrown = false;
        } catch (ValidationException $exception) {
            $thrown = true;
        }

        $this->assertFalse($thrown);
    }
}
