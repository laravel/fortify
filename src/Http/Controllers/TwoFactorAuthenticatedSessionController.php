<?php

namespace Laravel\Fortify\Http\Controllers;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Routing\Controller;
use Laravel\Fortify\Contracts\TwoFactorChallengeViewResponse;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse;
use Laravel\Fortify\Http\Requests\TwoFactorLoginRequest;
use Laravel\Fortify\Http\Responses\FailedTwoFactorLoginResponse;

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
     * @param  \Laravel\Fortify\Http\Requests\TwoFactorLoginRequest  $request
     * @return \Laravel\Fortify\Contracts\TwoFactorChallengeViewResponse
     */
    public function create(TwoFactorLoginRequest $request)
    {
        if ($this->isRecentlyConfirmed()) {
            $user = $request->challengedUser();

            if ($request->session()->pull('2fa.user_id') === $user->id) {
                $this->guard->login($user, $request->remember());

                return app(TwoFactorLoginResponse::class);
            }
        }

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

        if ($code = $request->validRecoveryCode()) {
            $user->replaceRecoveryCode($code);
        } elseif (! $request->hasValidCode()) {
            return app(FailedTwoFactorLoginResponse::class);
        }

        $this->guard->login($user, $request->remember());

        if ($request->filled('remember2fa')) {
            $request->session()->put(['2fa' => [
                'confirmed_at' => time(),
                'user_id' => $user->getKey(),
            ]]);
        } else {
            $request->session()->forget('2fa');
        }

        return app(TwoFactorLoginResponse::class);
    }

    protected function isRecentlyConfirmed()
    {
        $maximumSecondsSinceConfirmation = config('fortify.two_factor_timeout', 0);

        return (time() - session('2fa.confirmed_at', 0)) < $maximumSecondsSinceConfirmation;
    }
}
