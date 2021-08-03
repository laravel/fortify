<?php

namespace Laravel\Fortify\Actions;

use Laravel\Fortify\Fortify;

class AttemptToAuthenticate extends AbstractAuthenticate
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  callable  $next
     * @return mixed
     */
    public function handle($request, $next)
    {
        if (Fortify::$authenticateUsingCallback) {
            $this->handleUsingCustomCallback($request);

            return $next($request);
        }

        if ($request->user() || $this->guard->attempt(
            $request->only(Fortify::username(), 'password'),
            $request->filled('remember'))
        ) {
            return $next($request);
        }

        $this->throwFailedAuthenticationException($request);
    }
}
