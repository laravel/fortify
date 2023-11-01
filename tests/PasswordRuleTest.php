<?php

namespace Laravel\Fortify\Tests;

use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Validator;
use Laravel\Fortify\Rules\Password;

class PasswordRuleTest extends OrchestraTestCase
{
    /**
     * The Password validation rule instance.
     *
     * @var \Laravel\Fortify\Rules\Password
     */
    protected $rule;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->rule = new Password();
    }

    /**
     * Data provider containing valid numeric passwords.
     *
     * @return array[]
     */
    public function provideNumericPasswords(): array
    {
        return [
            ['p4ssw0rd'],
            [1234567890],
        ];
    }

    /**
     * Data provider containing valid passwords with special characters.
     *
     * @return array[]
     */
    public function provideSpecialCharacterPasswords(): array
    {
        return [
            ['pa$$word'],
            ['p+sw-ord'],
        ];
    }

    /**
     * Data provider containing valid uppercase passwords.
     *
     * @return array[]
     */
    public function provideUppercasePasswords(): array
    {
        return [
            ['Password'],
            ['passWord'],
        ];
    }

    /**
     * Create a new Validator instance using the "Password" validation rule
     * and a given password string.
     *
     * @param  string  $password
     * @return \Illuminate\Validation\Validator
     */
    public function validator(string $password): Validator
    {
        return ValidatorFacade::make(['password' => $password], ['password' => $this->rule]);
    }

    /**
     * Test if the "Password" rule can require numeric characters.
     *
     * @dataProvider provideNumericPasswords
     * @return void
     */
    public function test_password_rule_can_require_numeric_characters($password)
    {
        $this->rule->requireNumeric();

        $this->assertTrue($this->validator($password)->passes());
    }

    /**
     * Test if the "Password" rule can require special characters.
     *
     * @dataProvider provideSpecialCharacterPasswords
     * @return void
     */
    public function test_password_rule_can_require_special_characters($password)
    {
        $this->rule->requireSpecialCharacter();

        $this->assertTrue($this->validator($password)->passes());
    }

    /**
     * Test if the "Password" rule can require numeric characters.
     *
     * @dataProvider provideUppercasePasswords
     * @return void
     */
    public function test_password_rule_can_require_uppercase_characters($password)
    {
        $this->rule->requireUppercase();

        $this->assertTrue($this->validator($password)->passes());
    }

    /**
     * Test if the "Password" rule can require a minimum length.
     *
     * @return void
     */
    public function test_password_rule_can_require_minimum_length()
    {
        $this->rule->length(6);
        $this->assertTrue($this->validator('admin')->fails());

        $this->rule->length(8);
        $this->assertTrue($this->validator(1234567)->fails());
    }

    /**
     * Test if the "Password" rule can return an error message when
     * it requires a numeric character.
     *
     * @return void
     */
    public function test_numeric_rule_can_return_error_message()
    {
        $this->rule->requireNumeric();

        $this->assertStringContainsString('one number', $this->validator('password')->errors()->first());
    }

    /**
     * Test if the "Password" rule can return an error message when it
     * requires a special character.
     *
     * @return void
     */
    public function test_special_character_rule_can_return_error_message()
    {
        $this->rule->requireSpecialCharacter();

        $this->assertStringContainsString('one special character', $this->validator('password')->errors()->first());
    }

    /**
     * Test if the "Password" rule can return an error message when it
     * requires an uppercase character.
     *
     * @return void
     */
    public function test_uppercase_rule_can_return_error_message()
    {
        $this->rule->requireUppercase();

        $this->assertStringContainsString('one uppercase character', $this->validator('password')->errors()->first());
    }

    /**
     * Test if the "Password" rule can return an error message when it
     * requires a minimum length.
     *
     * @return void
     */
    public function test_length_rule_can_return_error_message()
    {
        $this->rule->length(10);

        $this->assertStringContainsString('must be at least 10 characters', $this->validator('password')->errors()->first());
    }
}
