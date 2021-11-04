<?php

namespace Laravel\Fortify\Http\Controllers;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Routing\Controller;
use Laravel\Fortify\Actions\ConfirmTwoFactor;
use Laravel\Fortify\Contracts\ConfirmTwoFactorViewResponse;
use Laravel\Fortify\Contracts\TwoFactorConfirmedResponse;
use Laravel\Fortify\Contracts\UserTwoFactorNotEnabledViewResponse;
use Laravel\Fortify\Http\Requests\TwoFactorConfirmationRequest;

class ConfirmableTwoFactorController extends Controller
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
     * @param  \Illuminate\Contracts\Auth\StatefulGuard  $guard
     * @return void
     */
    public function __construct(StatefulGuard $guard)
    {
        $this->guard = $guard;
    }

    /**
     * Show the confirm two factor view.
     *
     * @param  \Laravel\Fortify\Http\Requests\TwoFactorConfirmationRequest  $request
     * @return \Laravel\Fortify\Contracts\ConfirmTwoFactorViewResponse
     */
    public function show(TwoFactorConfirmationRequest $request): ConfirmTwoFactorViewResponse
    {
        if ($request->user()->isTwoFactorDisabled()) {
            return app(UserTwoFactorNotEnabledViewResponse::class);
        }

        return app(ConfirmTwoFactorViewResponse::class);
    }

    /**
     * Confirm the user's two factor.
     *
     * @param  \Laravel\Fortify\Http\Requests\TwoFactorConfirmationRequest  $request
     * @return mixed
     */
    public function store(TwoFactorConfirmationRequest $request)
    {
        $confirmed = app(ConfirmTwoFactor::class)(
            $this->guard, $request, $request->user()
        );

        if ($confirmed instanceof TwoFactorConfirmedResponse) {
            $request->session()->put('auth.two_factor_confirmed_at', time());
        }

        return $confirmed;
    }
}
