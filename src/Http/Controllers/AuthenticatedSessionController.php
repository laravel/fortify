<?php

namespace Laravel\Fortify\Http\Controllers;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Pipeline;
use Laravel\Fortify\Actions\AttemptToAuthenticate;
use Laravel\Fortify\Actions\CanonicalizeUsername;
use Laravel\Fortify\Actions\EnsureLoginIsNotThrottled;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\LoginViewResponse;
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Requests\LoginRequest;

class AuthenticatedSessionController extends Controller {

    public function __construct(StatefulGuard $guard) {

        $this->guard = $guard;

    }

    public function create(Request $request): LoginViewResponse {

        return app(LoginViewResponse::class);

    }

    public function store(LoginRequest $request) {

        return $this->loginPipeline($request)->then(function ($request) {

            return app(LoginResponse::class);

        });

    }

    protected function loginPipeline(LoginRequest $request) {
        
        if(Fortify::$authenticateThroughCallback) {

            return (new Pipeline(app()))->send($request)->through(array_filter (

                call_user_func(Fortify::$authenticateThroughCallback, $request)

            ));

        }

        if(is_array(config('fortify.pipelines.login'))) {

            return (new Pipeline(app()))->send($request)->through(array_filter(

                config('fortify.pipelines.login')

            ));

        }

        return (new Pipeline(app()))->send($request)->through(array_filter([

            config('fortify.limiters.login') ? null : EnsureLoginIsNotThrottled::class,
            config('fortify.lowercase_usernames') ? CanonicalizeUsername::class : null,
            Features::enabled(Features::twoFactorAuthentication()) ? RedirectIfTwoFactorAuthenticatable::class : null,
            AttemptToAuthenticate::class,
            PrepareAuthenticatedSession::class,

        ]));

    }

    public function destroy(Request $request): LogoutResponse {

        $this->guard->logout();

        if($request->hasSession()) {

            $request->session()->invalidate();
            $request->session()->regenerateToken();

        }

        return app(LogoutResponse::class);

    }

}