<?php

namespace Laravel\Fortify\Http\Controllers;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravel\Fortify\Contracts\FailedPasswordVerifyResponse;
use Laravel\Fortify\Contracts\PasswordVerifiedResponse;
use Laravel\Fortify\Contracts\VerifyPasswordViewResponse;

class VerifyPasswordController extends Controller
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
     * Show the verify password view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Laravel\Fortify\Contracts\VerifyPasswordViewResponse
     */
    public function show(Request $request, VerifyPasswordViewResponse $response): VerifyPasswordViewResponse
    {
        return $response;
    }

    /**
     * Verify the user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Responsable
     */
    public function store(Request $request): Responsable
    {
        $username = config('fortify.username');
        if ($status = $this->guard->validate([
            $username => $request->user()->{$username},
            'password' => $request->input('password'),
        ])) {
            $request->session()->put('auth.password_confirmed_at', time());
        }

        return $status
                    ? app(PasswordVerifiedResponse::class)
                    : app(FailedPasswordVerifyResponse::class);
    }
}
