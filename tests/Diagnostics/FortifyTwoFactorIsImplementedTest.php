<?php

namespace Laravel\Fortify\Tests\Diagnostics;

use Illuminate\Support\Facades\Schema;
use Laravel\Doctor\Results\Status;
use Laravel\Fortify\Diagnostics\FortifyTwoFactorIsImplemented;
use Laravel\Fortify\Features;
use Laravel\Fortify\Tests\Models\UserWithTwoFactor;

class FortifyTwoFactorIsImplementedTest extends DiagnosticsTestCase
{
    protected function withTwoFactorUser(array $options = [], bool $withColumns = true, bool $withConfirmedAt = false): void
    {
        config([
            'fortify.features' => [Features::twoFactorAuthentication($options)],
            'auth.providers.users.model' => UserWithTwoFactor::class,
        ]);

        Schema::create('users', function ($table) use ($withColumns, $withConfirmedAt) {
            $table->increments('id');
            $table->string('email');

            if ($withColumns) {
                $table->text('two_factor_secret')->nullable();
                $table->text('two_factor_recovery_codes')->nullable();
            }

            if ($withConfirmedAt) {
                $table->timestamp('two_factor_confirmed_at')->nullable();
            }
        });
    }

    public function test_diagnostic_skips_when_the_feature_is_not_used()
    {
        config(['fortify.features' => []]);

        $result = (new FortifyTwoFactorIsImplemented)->check();

        $this->assertSame(Status::Skip, $result->status);
        $this->assertSame('Two-factor authentication is not used.', $result->summary);
    }

    public function test_diagnostic_fails_when_the_model_is_missing_the_trait()
    {
        config(['fortify.features' => [Features::twoFactorAuthentication()]]);

        $result = (new FortifyTwoFactorIsImplemented)->check();

        $this->assertSame(Status::Fail, $result->status);
        $this->assertStringContainsString('does not use the TwoFactorAuthenticatable trait', $result->summary);
    }

    public function test_diagnostic_fails_when_the_columns_are_missing()
    {
        $this->withTwoFactorUser(withColumns: false);

        $result = (new FortifyTwoFactorIsImplemented)->check();

        $this->assertSame(Status::Fail, $result->status);
        $this->assertSame('The [users] table is missing the two-factor authentication columns.', $result->summary);
        $this->assertStringContainsString('two_factor_secret', (string) $result->details);
        $this->assertStringNotContainsString('two_factor_confirmed_at', (string) $result->details);
    }

    public function test_diagnostic_requires_the_confirmation_column_when_confirmation_is_enabled()
    {
        $this->withTwoFactorUser(['confirm' => true]);

        $result = (new FortifyTwoFactorIsImplemented)->check();

        $this->assertSame(Status::Fail, $result->status);
        $this->assertStringContainsString('two_factor_confirmed_at', (string) $result->details);
    }

    public function test_diagnostic_does_not_require_the_confirmation_column_when_confirmation_is_disabled()
    {
        $this->withTwoFactorUser();

        $result = (new FortifyTwoFactorIsImplemented)->check();

        $this->assertSame(Status::Pass, $result->status);
    }

    public function test_diagnostic_passes_when_the_trait_and_columns_are_present()
    {
        $this->withTwoFactorUser(['confirm' => true], withConfirmedAt: true);

        $result = (new FortifyTwoFactorIsImplemented)->check();

        $this->assertSame(Status::Pass, $result->status);
    }
}
