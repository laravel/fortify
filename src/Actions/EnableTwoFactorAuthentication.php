<?php

namespace Laravel\Fortify\Actions;

use Illuminate\Support\Collection;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\Events\TwoFactorAuthenticationEnabled;
use Laravel\Fortify\RecoveryCode;

class EnableTwoFactorAuthentication {

    protected $provider;

    public function __construct(TwoFactorAuthenticationProvider $provider) {

        $this->provider = $provider;

    }

    public function __invoke($user) {

        $user->forceFill([

            'two_factor_secret' => encrypt($this->provider->generateSecretKey()),
            'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, function () {

                return RecoveryCode::generate();

            })->all())),

        ])->save();

        TwoFactorAuthenticationEnabled::dispatch($user);

    }

}