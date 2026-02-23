<?php

namespace Laravel\Fortify\Tests;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FortifyRateLimiterTest extends OrchestraTestCase
{
    use RefreshDatabase;

    public function test_login_rate_limiter_can_be_overridden()
    {
        $key = 'login-test-key';

        RateLimiter::for('login', function () use ($key) {
            return Limit::perMinute(2)->by($key);
        });

        $this->assertFalse(RateLimiter::tooManyAttempts($key, 2));
        RateLimiter::hit($key);

        $this->assertFalse(RateLimiter::tooManyAttempts($key, 2));
        RateLimiter::hit($key);

        $this->assertTrue(RateLimiter::tooManyAttempts($key, 2));
        RateLimiter::clear($key);
    }

    public function test_two_factor_rate_limiter_can_be_overridden()
    {
        $key = '2fa-test-key';

        RateLimiter::for('two-factor', function () use ($key) {
            return Limit::perMinute(3)->by($key);
        });

        $this->assertFalse(RateLimiter::tooManyAttempts($key, 3));
        RateLimiter::hit($key);
        $this->assertFalse(RateLimiter::tooManyAttempts($key, 3));
        RateLimiter::hit($key);
        $this->assertFalse(RateLimiter::tooManyAttempts($key, 3));
        RateLimiter::hit($key);

        $this->assertTrue(RateLimiter::tooManyAttempts($key, 3));
        RateLimiter::clear($key);
    }

    public function test_password_reset_rate_limiter_can_be_overridden()
    {
        $key = 'reset-test-key';

        RateLimiter::for('password-reset', function () use ($key) {
            return Limit::perMinute(4)->by($key);
        });

        for ($i = 0; $i < 4; $i++) {
            $this->assertFalse(RateLimiter::tooManyAttempts($key, 4));
            RateLimiter::hit($key);
        }

        $this->assertTrue(RateLimiter::tooManyAttempts($key, 4));
        RateLimiter::clear($key);
    }
}
