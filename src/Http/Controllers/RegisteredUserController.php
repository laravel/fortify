<?php

namespace Laravel\Fortify\Http\Controllers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Contracts\RegisterViewResponse;
use Illuminate\Pipeline\Pipeline;
use Laravel\Fortify\Actions\AttemptToLogin;

class RegisteredUserController extends Controller
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
     * Show the registration view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Laravel\Fortify\Contracts\RegisterViewResponse
     */
    public function create(Request $request): RegisterViewResponse
    {
        return app(RegisterViewResponse::class);
    }

    /**
     * Create a new registered user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Laravel\Fortify\Contracts\CreatesNewUsers  $creator
     * @return mixed
     */
    public function store(Request $request, CreatesNewUsers $creator)
    {
        event(new Registered($user = $creator->create($request->all())));

        return $this->registerPipeline($request)->then(function ($request) {
            return app(RegisterResponse::class);
        });
    }

    /**
     * Get the authentication pipeline instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Pipeline\Pipeline
     */
    protected function registerPipeline(Request $request)
    {
        if (is_array(config('fortify.pipelines.register'))) {
            return (new Pipeline(app()))->send($request)->through(array_filter(
                config('fortify.pipelines.register')
            ));
        }

        return (new Pipeline(app()))->send($request)->through([
            AttemptToLogin::class,
        ]);
    }
}
