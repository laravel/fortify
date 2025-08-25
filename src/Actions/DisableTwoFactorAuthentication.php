<?php

namespace Laravel\Fortify\Actions;

use Laravel\Fortify\Events\TwoFactorAuthenticationDisabled;
use Laravel\Fortify\Fortify;

class DisableTwoFactorAuthentication
{
    /**
     * Disable two factor authentication for the user.
     *
     * @param  mixed  $user
     * @return void
     */
    public function __invoke($user)
    {
        if (! is_null($user->two_factor_secret) ||
            ! is_null($user->two_factor_recovery_codes) ||
            ! is_null($user->two_factor_confirmed_at)) {
            $user->forceFill([
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
            ] + (Fortify::confirmsTwoFactorAuthentication() ? [
                'two_factor_confirmed_at' => null,
            ] : []))->save();

            TwoFactorAuthenticationDisabled::dispatch($user);
        }
    }
}
