<?php

namespace Laravel\Fortify\Http\Controllers;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravel\Fortify\Contracts\FailedTwoFactorLoginResponse;
use Laravel\Fortify\Contracts\TwoFactorChallengeViewResponse;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse;
use Laravel\Fortify\Http\Requests\TwoFactorLoginRequest;

class TwoFactorAuthenticatedSessionController extends Controller
{
    /**
     * Show the two factor authentication challenge view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Laravel\Fortify\Contracts\TwoFactorChallengeViewResponse
     */
    public function create(Request $request): TwoFactorChallengeViewResponse
    {
        return app(TwoFactorChallengeViewResponse::class);
    }

    /**
     * Attempt to authenticate a new session using the two factor authentication code.
     *
     * @param  \Laravel\Fortify\Http\Requests\TwoFactorLoginRequest  $request
     * @param  \Illuminate\Contracts\Auth\StatefulGuard $guard
     * @return mixed
     */
    public function store(TwoFactorLoginRequest $request, StatefulGuard $guard)
    {
        $user = $request->challengedUser();

        if ($code = $request->validRecoveryCode()) {
            $user->replaceRecoveryCode($code);
        } elseif (! $request->hasValidCode()) {
            return app(FailedTwoFactorLoginResponse::class);
        }

        $guard->login($user, $request->remember());

        return app(TwoFactorLoginResponse::class);
    }
}
