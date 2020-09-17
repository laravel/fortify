<?php

namespace Laravel\Fortify\Http\Controllers;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravel\Fortify\Contracts\TwoFactorChallengeViewResponse;
use Laravel\Fortify\Http\Requests\TwoFactorLoginRequest;
use Laravel\Fortify\Http\Responses\FailedTwoFactorLoginResponse;
use Laravel\Fortify\Http\Responses\TwoFactorLoginResponse;

class TwoFactorAuthenticatedSessionController extends Controller
{
    /**
     * The guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected $guard;

    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Contracts\Auth\StatefulGuard
     * @return void
     */
    public function __construct(StatefulGuard $guard)
    {
        $this->guard = $guard;
    }

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
     * @return mixed
     */
    public function store(TwoFactorLoginRequest $request)
    {
        $user = $request->challengedUser();
        $timestamp = false;

        if ($code = $request->validRecoveryCode()) {
            $user->replaceRecoveryCode($code);
        } elseif (! $timestamp = $request->hasValidCode()) {
            return app(FailedTwoFactorLoginResponse::class);
        }

        if ($timestamp) {
            cache()->put("user-{$user->id}-2fa-timestamp", $timestamp, now()->addMinutes(1));
        }

        $this->guard->login($user, $request->remember());

        return app(TwoFactorLoginResponse::class);
    }
}
