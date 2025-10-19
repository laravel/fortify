<?php

namespace Laravel\Fortify\Actions;

use Laravel\Fortify\LoginRateLimiter;
use Laravel\Fortify\ValidatesCredentials;
use Illuminate\Contracts\Auth\StatefulGuard;

class AttemptToAuthenticate
{
    use ValidatesCredentials;

    /**
     * The guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected $guard;

    /**
     * The login rate limiter instance.
     *
     * @var \Laravel\Fortify\LoginRateLimiter
     */
    protected $limiter;

    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Contracts\Auth\StatefulGuard  $guard
     * @param  \Laravel\Fortify\LoginRateLimiter  $limiter
     * @return void
     */
    public function __construct(StatefulGuard $guard, LoginRateLimiter $limiter)
    {
        $this->guard = $guard;
        $this->limiter = $limiter;
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
        $user = $this->guard->user() ?? $this->validateCredentials($request);

        $this->guard->login($user, $request->boolean('remember'));
        
        return $next($request);
    }
}
