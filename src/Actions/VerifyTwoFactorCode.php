<?php

namespace Laravel\Fortify\Actions;

use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\Fortify;

class VerifyTwoFactorCode
{
    /**
     * The two factor authentication provider.
     *
     * @var \Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider
     */
    protected $provider;

    /**
     * Create a new action instance.
     *
     * @param  \Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider  $provider
     * @return void
     */
    public function __construct(TwoFactorAuthenticationProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Verify the two factor authentication code for the user.
     *
     * @param  mixed  $user
     * @param  string|null  $code
     * @return bool
     */
    public function __invoke($user, ?string $code = null): bool
    {
        if (empty($code) || ! $user->hasEnabledTwoFactorAuthentication()) {
            return false;
        }

        return $this->provider->verify(
            Fortify::currentEncrypter()->decrypt($user->two_factor_secret), $code
        );
    }
}
