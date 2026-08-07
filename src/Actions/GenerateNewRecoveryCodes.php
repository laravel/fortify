<?php

namespace Laravel\Fortify\Actions;

use Illuminate\Support\Collection;
use Laravel\Fortify\Events\RecoveryCodesGenerated;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\RecoveryCode;

class GenerateNewRecoveryCodes
{
    /**
     * Generate new recovery codes for the user.
     *
     * @param  mixed  $user
     * @return void
     */
    public function __invoke($user)
    {
        $user->forceFill([
            'two_factor_recovery_codes' => Fortify::currentEncrypter()->encrypt(json_encode(Collection::times(8, function () {
                return RecoveryCode::generate();
            })->all())),
        ])->save();

        RecoveryCodesGenerated::dispatch($user);
    }
}
