<?php

namespace Laravel\Fortify;

use Illuminate\Auth\Events\Failed;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;

class CredentialsValidator
{
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
     * Attempt to validate the credentials given in the request.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    public function validateCredentials($request)
    {
        if ($user = $this->guard->user()) {
            return $user;
        }

        if (Fortify::$authenticateUsingCallback) {
            $user = $this->handleUsingCustomCallback($request);
        } else {
            $this->guard->once($request->only(Fortify::username(), 'password'));
            $user = $this->guard->user();
        }

        if (! $user) {
            $this->throwFailedAuthenticationException($request);
        }

        return $user;
    }

    /**
     * Attempt to authenticate using a custom callback.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    protected function handleUsingCustomCallback($request)
    {
        return tap(call_user_func(Fortify::$authenticateUsingCallback, $request), function ($user) use ($request) {
            if ($user) {
                $this->guard->setUser($user);
            } else {
                $this->fireFailedEvent($request);
            }
        });
    }

    /**
     * Throw a failed authentication validation exception.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function throwFailedAuthenticationException($request)
    {
        $this->limiter->increment($request);
        
        throw ValidationException::withMessages([
            Fortify::username() => [trans('auth.failed')],
        ]);
    }

    /**
     * Fire the failed authentication attempt event with the given arguments.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function fireFailedEvent($request)
    {
        event(new Failed($this->guard->name ?? config('fortify.guard'), null, $request->only(Fortify::username(), 'password')));
    }

    /**
     * Get the guard implementation.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    public function getGuard()
    {
        return $this->guard;
    }
}
