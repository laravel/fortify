<?php

namespace Laravel\Fortify\Actions;

use Illuminate\Auth\Events\Failed;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\LoginRateLimiter;
use Laravel\Fortify\TwoFactorAuthenticatable;

class RedirectIfTwoFactorAuthenticatable
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
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  callable  $next
     * @return mixed
     */
    public function handle($request, $next)
    {
        $user = $this->validateCredentials($request);

        if (optional($user)->two_factor_secret &&
            in_array(TwoFactorAuthenticatable::class, class_uses_recursive($user))) {
            return $this->twoFactorChallengeResponse($request, $user);
        }

        return $next($request);
    }

    /**
     * Attempt to validate the incoming credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    protected function validateCredentials($request)
    {
        if (Fortify::$authenticateUsingCallback) {
            return tap(call_user_func(Fortify::$authenticateUsingCallback, $request), function ($user) use ($request) {
                if (! $user) {
                    $this->fireFailedEvent($request);

                    $this->throwFailedAuthenticationException($request);
                }
            });
        }

        $model = $this->guard->getProvider()->getModel();

        return tap($model::where(Fortify::username(), $request->{Fortify::username()})->first(), function ($user) use ($request) {
            if (! $user || ! $this->guard->getProvider()->validateCredentials($user, ['password' => $request->password])) {
                $this->fireFailedEvent($request, $user);

                $this->throwFailedAuthenticationException($request);
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
     * @param  \Illuminate\Contracts\Auth\Authenticatable|null  $user
     * @return void
     */
    protected function fireFailedEvent($request, $user = null)
    {
        event(new Failed(config('fortify.guard'), $user, [
            Fortify::username() => $request->{Fortify::username()},
            'password' => $request->password,
        ]));
    }

    /**
     * Get the two factor authentication enabled response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function twoFactorChallengeResponse($request, $user)
    {
        $request->session()->put([
            'login.id' => $user->getKey(),
            'login.remember' => $request->filled('remember'),
        ]);

        return $request->wantsJson()
                    ? response()->json(['two_factor' => true])
                    : redirect()->route('two-factor.login');
    }
}
