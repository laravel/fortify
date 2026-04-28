<?php

namespace Laravel\Fortify\Tests;

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Laravel\Passkeys\Http\Controllers\PasskeyConfirmationController;
use Laravel\Passkeys\Http\Controllers\PasskeyLoginController;
use Orchestra\Testbench\Attributes\DefineEnvironment;

#[DefineEnvironment('withPasskeys')]
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

        $this->assertTrue(Route::has('passkey.login-options'));
        $this->assertTrue(Route::has('passkey.login'));
        $this->assertTrue(Route::has('passkey.confirm-options'));
        $this->assertTrue(Route::has('passkey.confirm'));
        $this->assertTrue(Route::has('passkey.registration-options'));
        $this->assertTrue(Route::has('passkey.store'));
        $this->assertTrue(Route::has('passkey.destroy'));
    }

    public function test_passkeys_routes_use_the_expected_passkeys_controllers()
    {
        $verify = Route::getRoutes()->getByName('passkey.login');
        $confirm = Route::getRoutes()->getByName('passkey.confirm');

        $this->assertNotNull($verify);
        $this->assertNotNull($confirm);
        $this->assertSame(PasskeyLoginController::class.'@store', $verify->getActionName());
        $this->assertSame(PasskeyConfirmationController::class.'@store', $confirm->getActionName());
    }

    #[DefineEnvironment('withoutPasskeys')]
    public function test_passkeys_routes_are_not_registered_when_feature_is_disabled()
    {
        $this->assertFalse(Features::enabled(Features::passkeys()));

        $this->assertFalse(Route::has('passkey.login-options'));
        $this->assertFalse(Route::has('passkey.login'));
        $this->assertFalse(Route::has('passkey.confirm-options'));
        $this->assertFalse(Route::has('passkey.confirm'));
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
        $this->assertSame(config('fortify.passkeys.allowed_origins'), config('passkeys.allowed_origins'));
        $this->assertSame(config('fortify.passkeys.user_handle_secret'), config('passkeys.user_handle_secret'));
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
        $route = Route::getRoutes()->getByName('passkey.login-options');

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

    #[DefineEnvironment('withPasskeysConfirmingPasswords')]
    public function test_passkey_confirmation_routes_are_not_protected_by_password_confirmation_middleware()
    {
        $route = Route::getRoutes()->getByName('passkey.confirm');

        $this->assertNotNull($route);
        $this->assertNotContains('password.confirm', $route->middleware());
    }

    public function test_passkeys_user_model_resolves_from_custom_guard()
    {
        config([
            'fortify.guard' => 'admin',
            'auth.guards.admin' => ['driver' => 'session', 'provider' => 'admins'],
            'auth.providers.admins' => ['driver' => 'eloquent', 'model' => 'App\\Models\\Admin'],
        ]);

        $this->assertSame('App\\Models\\Admin', $this->resolvePasskeyUserModel());
    }

    public function test_passkeys_user_model_falls_back_when_guard_has_no_provider()
    {
        config([
            'fortify.guard' => 'ghost',
            'auth.guards.ghost' => ['driver' => 'session'],
            'auth.defaults.provider' => null,
            'auth.providers.users.model' => 'App\\Models\\FallbackUser',
        ]);

        $this->assertSame('App\\Models\\FallbackUser', $this->resolvePasskeyUserModel());
    }

    protected function resolvePasskeyUserModel(): ?string
    {
        $provider = $this->app->getProvider(\Laravel\Fortify\FortifyServiceProvider::class);
        $method = new \ReflectionMethod($provider, 'passkeyUserModel');
        $method->setAccessible(true);

        return $method->invoke($provider);
    }
}
