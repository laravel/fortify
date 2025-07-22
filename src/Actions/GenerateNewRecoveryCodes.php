<?php

namespace Laravel\Fortify\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Laravel\Fortify\Events\RecoveryCodesGenerated;
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
            'two_factor_recovery_codes' => (Model::$encrypter ?? Crypt::getFacadeRoot())->encrypt(json_encode(Collection::times(8, function () {
                return RecoveryCode::generate();
            })->all())),
        ])->save();

        RecoveryCodesGenerated::dispatch($user);
    }
}
