<?php

namespace Laravel\Fortify\Tests;

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Orchestra\Testbench\Attributes\DefineEnvironment;

class PasskeyTest extends OrchestraTestCase
{
    public function test_passkeys_package_passkey_model_is_used_by_default()
    {
        $this->assertSame(\Laravel\Passkeys\Passkey::class, \Laravel\Passkeys\Passkeys::passkeyModel());
    }

    public function test_passkeys_routes_are_registered_by_default()
    {
        $this->assertTrue(Features::enabled(Features::passkeys()));
        $this->assertTrue(Features::canManagePasskeys());
        $this->assertTrue(Features::hasSecurityFeatures());
        $this->assertTrue(Features::hasProfileFeatures());

        $this->assertTrue(Route::has('passkey.verification-options'));
        $this->assertTrue(Route::has('passkey.verify'));
        $this->assertTrue(Route::has('passkey.registration-options'));
        $this->assertTrue(Route::has('passkey.store'));
        $this->assertTrue(Route::has('passkey.destroy'));
    }

    #[DefineEnvironment('withoutPasskeys')]
    public function test_passkeys_routes_are_not_registered_when_feature_is_disabled()
    {
        $this->assertFalse(Features::enabled(Features::passkeys()));

        $this->assertFalse(Route::has('passkey.verification-options'));
        $this->assertFalse(Route::has('passkey.verify'));
        $this->assertFalse(Route::has('passkey.registration-options'));
        $this->assertFalse(Route::has('passkey.store'));
        $this->assertFalse(Route::has('passkey.destroy'));
    }

    #[DefineEnvironment('withPasskeys')]
    public function test_passkeys_configuration_is_synchronized_with_fortify_configuration()
    {
        $this->assertSame(config('fortify.guard'), config('passkeys.guard'));
        $this->assertSame(config('fortify.middleware'), config('passkeys.middleware'));
        $this->assertSame(config('fortify.passkeys.relying_party_id'), config('passkeys.relying_party_id'));
        $this->assertSame(config('fortify.passkeys.timeout'), config('passkeys.timeout'));
        $this->assertSame(Fortify::redirects('login'), config('passkeys.redirect'));
        $this->assertSame(
            config('fortify.limiters.passkeys') ? 'throttle:'.config('fortify.limiters.passkeys') : null,
            config('passkeys.throttle')
        );
    }

    #[DefineEnvironment('withPasskeysLimiter')]
    public function test_passkeys_routes_use_the_passkeys_limiter()
    {
        $route = Route::getRoutes()->getByName('passkey.verification-options');

        $this->assertNotNull($route);
        $this->assertContains('throttle:passkeys', $route->middleware());
    }

    #[DefineEnvironment('withPasskeysConfirmingPasswords')]
    public function test_passkeys_management_routes_can_require_password_confirmation()
    {
        $route = Route::getRoutes()->getByName('passkey.registration-options');

        $this->assertNotNull($route);
        $this->assertContains('password.confirm', $route->middleware());
    }
}
