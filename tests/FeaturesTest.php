<?php

namespace Laravel\Fortify\Tests;

use Laravel\Fortify\Features;

class FeaturesTest extends OrchestraTestCase
{
    public function test_password_confirmation_feature_enabled_by_default()
    {
        $this->assertTrue(Features::enabled(Features::passwordConfirmation()));
    }

    public function test_password_confirmation_feature_can_be_disabled()
    {
        config(['fortify.features' => []]);

        $this->assertFalse(Features::enabled(Features::passwordConfirmation()));
    }

    public function test_two_factor_authentication_with_confirm_password_option_enables_password_confirmation()
    {
        config(['fortify.features' => []]);

        $this->assertFalse(Features::enabled(Features::passwordConfirmation()));

        $options = ['confirmPassword' => true];

        $feature = Features::twoFactorAuthentication($options);

        $this->assertEquals('two-factor-authentication', $feature);
        $this->assertTrue(config('fortify-options.two-factor-authentication.confirmPassword'));
        $this->assertTrue(Features::enabled(Features::passwordConfirmation()));
    }

    public function test_two_factor_authentication_with_false_confirm_password_option_does_not_enable_password_confirmation()
    {
        config(['fortify.features' => []]);

        $this->assertFalse(Features::enabled(Features::passwordConfirmation()));

        $options = ['confirmPassword' => false];

        $feature = Features::twoFactorAuthentication($options);

        $this->assertEquals('two-factor-authentication', $feature);
        $this->assertFalse(config('fortify-options.two-factor-authentication.confirmPassword'));
        $this->assertFalse(Features::enabled(Features::passwordConfirmation()));
    }

    public function test_two_factor_authentication_without_options_does_not_enable_password_confirmation()
    {
        config(['fortify.features' => []]);
        $this->assertFalse(Features::enabled(Features::passwordConfirmation()));

        $feature = Features::twoFactorAuthentication();

        $this->assertEquals('two-factor-authentication', $feature);
        $this->assertNull(config('fortify-options.two-factor-authentication.confirmPassword'));
        $this->assertFalse(Features::enabled(Features::passwordConfirmation()));
    }
}
