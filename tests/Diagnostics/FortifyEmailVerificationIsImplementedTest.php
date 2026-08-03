<?php

namespace Laravel\Fortify\Tests\Diagnostics;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Route;
use Laravel\Doctor\Results\Status;
use Laravel\Fortify\Diagnostics\FortifyEmailVerificationIsImplemented;
use Laravel\Fortify\Features;

class FortifyEmailVerificationIsImplementedTest extends DiagnosticsTestCase
{
    public function test_diagnostic_passes_when_the_feature_and_interface_are_present()
    {
        config([
            'fortify.features' => [Features::emailVerification()],
            'auth.providers.users.model' => VerifyingUser::class,
        ]);

        $result = (new FortifyEmailVerificationIsImplemented)->check();

        $this->assertSame(Status::Pass, $result->status);
        $this->assertStringContainsString('implements MustVerifyEmail', $result->summary);
    }

    public function test_diagnostic_fails_when_the_model_is_missing_the_interface()
    {
        config(['fortify.features' => [Features::emailVerification()]]);

        $result = (new FortifyEmailVerificationIsImplemented)->check();

        $this->assertSame(Status::Fail, $result->status);
        $this->assertStringContainsString('does not implement MustVerifyEmail', $result->summary);
    }

    public function test_diagnostic_warns_when_the_interface_is_present_without_the_feature()
    {
        config([
            'fortify.features' => [],
            'auth.providers.users.model' => VerifyingUser::class,
        ]);

        $this->app['router']->setRoutes(new RouteCollection);

        $result = (new FortifyEmailVerificationIsImplemented)->check();

        $this->assertSame(Status::Warn, $result->status);
        $this->assertStringContainsString('the email verification feature is disabled', $result->summary);
    }

    public function test_diagnostic_passes_when_verification_routes_are_defined_outside_fortify()
    {
        config([
            'fortify.features' => [],
            'auth.providers.users.model' => VerifyingUser::class,
        ]);

        $this->app['router']->setRoutes(new RouteCollection);

        Route::get('/verify-email', fn () => null)->name('verification.notice');
        Route::get('/verify-email/{id}/{hash}', fn () => null)->name('verification.verify');
        $this->app['router']->getRoutes()->refreshNameLookups();

        $result = (new FortifyEmailVerificationIsImplemented)->check();

        $this->assertSame(Status::Pass, $result->status);
        $this->assertStringContainsString('defined outside Fortify', $result->summary);
    }

    public function test_diagnostic_skips_when_the_feature_is_not_used()
    {
        config(['fortify.features' => []]);

        $result = (new FortifyEmailVerificationIsImplemented)->check();

        $this->assertSame(Status::Skip, $result->status);
        $this->assertSame('Email verification is not used.', $result->summary);
    }
}

class VerifyingUser extends User implements MustVerifyEmail
{
    protected $table = 'users';
}
