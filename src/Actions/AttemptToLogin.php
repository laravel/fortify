<?php

namespace Laravel\Fortify\Actions;

use Illuminate\Contracts\Auth\StatefulGuard;
use Laravel\Fortify\Fortify;

class AttemptToLogin
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
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  callable  $next
     * @return mixed
     */
    public function handle($request, $next)
    {
        $this->guard->attempt($request->only(Fortify::username(), 'password'));

        return $next($request);
    }
}
