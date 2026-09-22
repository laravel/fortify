<?php

namespace Laravel\Fortify\Tests\Diagnostics;

use Laravel\Doctor\Facades\Doctor;
use Laravel\Doctor\Results\Status;
use Laravel\Fortify\Diagnostics\FortifyEmailVerificationIsImplemented;
use Laravel\Fortify\Diagnostics\FortifyGuardIsStateful;
use Laravel\Fortify\Diagnostics\FortifyPasskeysAreConfigured;
use Laravel\Fortify\Diagnostics\FortifyPasswordResetRouteIsDefined;
use Laravel\Fortify\Diagnostics\FortifyTwoFactorIsImplemented;

class FortifyGuardIsStatefulTest extends DiagnosticsTestCase
{
    public function test_diagnostics_are_registered_with_doctor()
    {
        $registered = Doctor::registered();

        $this->assertContains(FortifyGuardIsStateful::class, $registered);
        $this->assertContains(FortifyEmailVerificationIsImplemented::class, $registered);
        $this->assertContains(FortifyPasswordResetRouteIsDefined::class, $registered);
        $this->assertContains(FortifyTwoFactorIsImplemented::class, $registered);
        $this->assertContains(FortifyPasskeysAreConfigured::class, $registered);
    }

    public function test_diagnostic_passes_for_a_session_guard()
    {
        $result = (new FortifyGuardIsStateful)->check();

        $this->assertSame(Status::Pass, $result->status);
        $this->assertSame('The Fortify guard [web] is a stateful guard.', $result->summary);
    }

    public function test_diagnostic_fails_for_an_undefined_guard()
    {
        config(['fortify.guard' => 'missing']);

        $result = (new FortifyGuardIsStateful)->check();

        $this->assertSame(Status::Fail, $result->status);
        $this->assertSame('The Fortify guard [missing] is not defined in auth.guards.', $result->summary);
    }

    public function test_diagnostic_fails_for_a_stateless_guard()
    {
        config([
            'auth.guards.api' => ['driver' => 'token', 'provider' => 'users'],
            'fortify.guard' => 'api',
        ]);

        $result = (new FortifyGuardIsStateful)->check();

        $this->assertSame(Status::Fail, $result->status);
        $this->assertSame('The Fortify guard [api] is not a stateful guard.', $result->summary);
    }
}
