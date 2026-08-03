<?php

namespace Laravel\Fortify\Tests\Diagnostics;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Routing\RouteCollection;
use Laravel\Doctor\Results\Status;
use Laravel\Fortify\Diagnostics\FortifyPasswordResetRouteIsDefined;
use Laravel\Fortify\Features;

class FortifyPasswordResetRouteIsDefinedTest extends DiagnosticsTestCase
{
    public function test_diagnostic_skips_when_the_feature_is_disabled()
    {
        config(['fortify.features' => []]);

        $result = (new FortifyPasswordResetRouteIsDefined)->check();

        $this->assertSame(Status::Skip, $result->status);
        $this->assertSame('The password reset feature is disabled.', $result->summary);
    }

    public function test_diagnostic_passes_when_the_route_is_defined()
    {
        config(['fortify.features' => [Features::resetPasswords()]]);

        $result = (new FortifyPasswordResetRouteIsDefined)->check();

        $this->assertSame(Status::Pass, $result->status);
        $this->assertSame('The [password.reset] route is defined.', $result->summary);
    }

    public function test_diagnostic_fails_when_views_are_disabled_without_a_replacement_route()
    {
        config(['fortify.features' => [Features::resetPasswords()]]);

        $this->app['router']->setRoutes(new RouteCollection);

        $result = (new FortifyPasswordResetRouteIsDefined)->check();

        $this->assertSame(Status::Fail, $result->status);
        $this->assertStringContainsString('password.reset', $result->remediation);
    }

    public function test_diagnostic_passes_when_password_reset_notifications_use_a_custom_url()
    {
        config(['fortify.features' => [Features::resetPasswords()]]);

        $this->app['router']->setRoutes(new RouteCollection);
        ResetPassword::createUrlUsing(fn () => 'https://frontend.example.com/reset-password');

        try {
            $result = (new FortifyPasswordResetRouteIsDefined)->check();

            $this->assertSame(Status::Pass, $result->status);
            $this->assertSame('Password reset notifications customize their URL or mail message.', $result->summary);
        } finally {
            ResetPassword::$createUrlCallback = null;
        }
    }
}
