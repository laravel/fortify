<?php

namespace Laravel\Fortify\Tests;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\RedirectsIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;
use Orchestra\Testbench\Attributes\DefineEnvironment;

class FortifyServiceProviderTest extends OrchestraTestCase
{
    public function test_views_can_be_customized()
    {
        Fortify::loginView(function () {
            return 'foo';
        });

        $response = $this->get('/login');

        $response->assertOk();
        $this->assertSame('foo', $response->content());
    }

    public function test_customized_views_can_return_their_own_responsable()
    {
        Fortify::loginView(function () {
            return new class implements Responsable
            {
                public function toResponse($request)
                {
                    return new JsonResponse(['foo' => 'bar']);
                }
            };
        });

        $response = $this->get('/login');

        $response->assertOk();
        $response->assertExactJson(['foo' => 'bar']);
    }

    #[DefineEnvironment('withTwoFactorAuthentication')]
    public function test_redirect_if_two_factor_authenticatable_is_resolved_fresh_after_flushing_scoped_instances()
    {
        $instanceA = $this->app->make(RedirectsIfTwoFactorAuthenticatable::class);
        $instanceB = $this->app->make(RedirectsIfTwoFactorAuthenticatable::class);

        $this->assertSame($instanceA, $instanceB);

        $this->app->forgetScopedInstances();

        $instanceC = $this->app->make(RedirectsIfTwoFactorAuthenticatable::class);

        $this->assertNotSame($instanceA, $instanceC);
    }

    #[DefineEnvironment('withTwoFactorAuthentication')]
    public function test_redirect_if_two_factor_authenticatable_receives_fresh_guard_after_flushing_scoped_instances()
    {
        $instance = $this->app->make(RedirectsIfTwoFactorAuthenticatable::class);

        $guardProperty = new \ReflectionProperty($instance, 'guard');
        $guardBefore = $guardProperty->getValue($instance);

        // Simulate Octane request boundary.
        Auth::forgetGuards();
        $this->app->forgetScopedInstances();

        $freshInstance = $this->app->make(RedirectsIfTwoFactorAuthenticatable::class);

        $guardAfter = $guardProperty->getValue($freshInstance);

        $this->assertNotSame($guardBefore, $guardAfter);
    }

    #[DefineEnvironment('withTwoFactorAuthentication')]
    public function test_custom_redirect_if_two_factor_authenticatable_is_resolved_fresh_after_flushing_scoped_instances()
    {
        Fortify::redirectUserForTwoFactorAuthenticationUsing(TestRedirectIfTwoFactorAuthenticatable::class);

        $instanceA = $this->app->make(RedirectsIfTwoFactorAuthenticatable::class);

        $this->app->forgetScopedInstances();

        $instanceB = $this->app->make(RedirectsIfTwoFactorAuthenticatable::class);

        $this->assertNotSame($instanceA, $instanceB);
    }
}

class TestRedirectIfTwoFactorAuthenticatable implements RedirectsIfTwoFactorAuthenticatable
{
    public function handle($request, $next)
    {
        return $next($request);
    }
}
