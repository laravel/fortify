<?php

namespace Laravel\Fortify\Actions;

use Laravel\Fortify\Fortify;
use Laravel\Fortify\CredentialsValidator;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Fortify\Events\TwoFactorAuthenticationChallenged;

class RedirectIfTwoFactorAuthenticatable
{
    /**
     * The credentials validator instance.
     *
     * @var \Laravel\Fortify\CredentialsValidator
     */
    protected $validator;

    /**
     * Create a new controller instance.
     *
     * @param  \Laravel\Fortify\CredentialsValidator  $validator
     * @return void
     */
    public function __construct(CredentialsValidator $validator)
    {
        $this->validator = $validator;
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
        $user = $this->validator->validateCredentials($request);

        if (Fortify::confirmsTwoFactorAuthentication() &&
            is_null(optional($user)->two_factor_confirmed_at)) {
            return $next($request);
        }

        if (optional($user)->two_factor_secret &&
            in_array(TwoFactorAuthenticatable::class, class_uses_recursive($user))) {
            return $this->twoFactorChallengeResponse($request, $user);
        }

        return $next($request);
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
            'login.remember' => $request->boolean('remember'),
        ]);

        TwoFactorAuthenticationChallenged::dispatch($user);

        return $request->wantsJson()
                    ? response()->json(['two_factor' => true])
                    : redirect()->route('two-factor.login');
    }
}
