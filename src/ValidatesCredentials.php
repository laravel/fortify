<?php

namespace Laravel\Fortify;

use Illuminate\Auth\Events\Failed;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;

trait ValidatesCredentials
{
    /**
     * Attempt to validate the credentials given in the request.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    public function validateCredentials($request)
    {
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
        $user = call_user_func(Fortify::$authenticateUsingCallback, $request);

        if (! $user) {
            $this->fireFailedEvent($request);

            return null;
        }

        $this->guard->setUser($user);

        return $user;
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
        event(new Failed($this->guard?->name ?? config('fortify.guard'), null, [
            Fortify::username() => $request->{Fortify::username()},
            'password' => $request->password,
        ]));
    }
}
