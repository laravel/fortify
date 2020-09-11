<?php

namespace Laravel\Fortify\Http\Controllers;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravel\Fortify\Contracts\ConfirmPasswordViewResponse;
use Laravel\Fortify\Contracts\FailedPasswordConfirmationResponse;
use Laravel\Fortify\Contracts\PasswordConfirmedResponse;
use Laravel\Fortify\Fortify;

class ConfirmablePasswordController extends Controller
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
     * Show the confirm password view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Laravel\Fortify\Contracts\VerifyPasswordViewResponse
     */
    public function show(Request $request)
    {
        return app(ConfirmPasswordViewResponse::class);
    }

    /**
     * Confirm the user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Responsable
     */
    public function store(Request $request)
    {
        $username = config('fortify.username');

        $confirmed = is_null(Fortify::$confirmPasswordsUsingCallback) ?  $this->guard->validate([
            $username => $request->user()->{$username},
            'password' => $request->input('password')
        ]) : $this->confirmPasswordUsingCustomCallback($request);

        if ($confirmed) {
            $request->session()->put('auth.password_confirmed_at', time());
        }

        return $confirmed
                    ? app(PasswordConfirmedResponse::class)
                    : app(FailedPasswordConfirmationResponse::class);
    }

    /**
     * Confirm the user's password using a custom callback.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function confirmPasswordUsingCustomCallback(Request $request)
    {
        return call_user_func(
            Fortify::$confirmPasswordsUsingCallback,
            $request->user(),
            $request
        );
    }
}
