<?php

namespace Laravel\Fortify\Tests;

use Illuminate\Support\Str;
use Laravel\Fortify\Rules\Password;

class PasswordRuleTest extends OrchestraTestCase
{
    public function test_password_rule()
    {
        $rule = new Password;

        $this->assertTrue($rule->passes('password', 'password'));
        $this->assertTrue($rule->passes('password', 234234234));
        $this->assertFalse($rule->passes('password', ['foo' => 'bar']));
        $this->assertFalse($rule->passes('password', 'secret'));

        $this->assertTrue(Str::contains($rule->message(), 'must be at least 8 characters'));

        $rule->length(10);

        $this->assertFalse($rule->passes('password', 'password'));
        $this->assertTrue($rule->passes('password', 'password11'));

        $this->assertTrue(Str::contains($rule->message(), 'must be at least 10 characters'));

        $rule->length(8)->requireUppercase();

        $this->assertFalse($rule->passes('password', 'password'));
        $this->assertTrue($rule->passes('password', 'Password'));

        $this->assertTrue(Str::contains($rule->message(), 'characters and contain at least 1 uppercase character'));

        $rule->length(8)->requireNumeric();

        $this->assertFalse($rule->passes('password', 'Password'));
        $this->assertFalse($rule->passes('password', 'password1'));
        $this->assertTrue($rule->passes('password', 'Password1'));

        $this->assertTrue(Str::contains($rule->message(), 'characters and contain at least 1 uppercase character and 1 number'));

        $rule->length(8)->requireNumeric(4)->requireUppercase(8);

        $this->assertFalse($rule->passes('password', 'password'));
        $this->assertFalse($rule->passes('password', 'password1234'));
        $this->assertFalse($rule->passes('password', 'PASSWORD'));
        $this->assertTrue($rule->passes('password', 'PASSWORD1234'));

        $this->assertTrue(Str::contains($rule->message(), 'characters and contain at least 8 uppercase character and 4 number'));
    }

    public function test_password_rule_can_require_special_characters()
    {
        $rule = new Password;

        $rule->length(8)->requireSpecialCharacter();

        $this->assertTrue($rule->passes('password', 'password!'));
        $this->assertFalse($rule->passes('password', 'password'));

        $this->assertTrue(Str::contains($rule->message(), 'must be at least 8 characters'));
        $this->assertTrue(Str::contains($rule->message(), 'at least 1 special character'));

        $rule->length(8)->requireSpecialCharacter(2);

        $this->assertTrue($rule->passes('password', 'password!#'));
        $this->assertFalse($rule->passes('password', 'password'));

        $this->assertTrue(Str::contains($rule->message(), 'must be at least 8 characters'));
        $this->assertTrue(Str::contains($rule->message(), 'at least 2 special character'));
    }

    public function test_password_rule_can_require_numeric_and_special_characters()
    {
        $rule = new Password;

        $rule->length(10)->requireNumeric()->requireSpecialCharacter();

        $this->assertTrue($rule->passes('password', 'password5%'));
        $this->assertFalse($rule->passes('password', 'my-password'));

        $this->assertTrue(Str::contains($rule->message(), 'must be at least 10 characters'));
        $this->assertTrue(Str::contains($rule->message(), 'contain at least 1 special character'));
        $this->assertTrue(Str::contains($rule->message(), 'and 1 number'));

        $rule->length(10)->requireNumeric(3)->requireSpecialCharacter(2);

        $this->assertTrue($rule->passes('password', 'password532%!'));
        $this->assertFalse($rule->passes('password', 'my-password'));

        $this->assertTrue(Str::contains($rule->message(), 'must be at least 10 characters'));
        $this->assertTrue(Str::contains($rule->message(), 'contain at least 2 special character'));
        $this->assertTrue(Str::contains($rule->message(), 'and 3 number'));
    }
}
