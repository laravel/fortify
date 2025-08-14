<?php

namespace Laravel\Fortify\Http\Controllers;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravel\Fortify\Actions\ConfirmPassword;
use Laravel\Fortify\Contracts\ConfirmPasswordViewResponse;
use Laravel\Fortify\Contracts\FailedPasswordConfirmationResponse;
use Laravel\Fortify\Contracts\PasswordConfirmedResponse;
use Illuminate\Support\Facades\Auth;

class ConfirmablePasswordController extends Controller {

    protected $guard;

    public function __construct(StatefulGuard $guard, Request $request) {

        $this->guard = $guard;

    }

    public function show(Request $request) {
        
        return app(ConfirmPasswordViewResponse::class);

    }

    public function store(Request $request) {

        if (session('login') == 'ramal') {

            $this->guard = Auth::guard('ramal');

        } else {

            $this->guard = Auth::guard('web');

        }

        $confirmed = app(ConfirmPassword::class)(

            $this->guard, $request->user(), $request->input('password')

        );

        if($confirmed) {

            $request->session()->put('auth.password_confirmed_at', time());

        }

        if($confirmed) {

            session([

                "2fa" => "confirmation"

            ]);

        } else {

            session([

                "2fa" => "no_confirmed"

            ]);

        }

        return $confirmed
            ? app(PasswordConfirmedResponse::class)
            : app(FailedPasswordConfirmationResponse::class);

    }
}
