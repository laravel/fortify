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
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
        ] + (Fortify::confirmsTwoFactorAuthentication() ? [
            'two_factor_confirmed_at' => null,
        ] : []))->save();

        TwoFactorAuthenticationDisabled::dispatch($user);
    }
}
