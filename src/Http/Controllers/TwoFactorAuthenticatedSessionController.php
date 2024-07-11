<?php

namespace Laravel\Fortify\Http\Controllers;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Contracts\FailedTwoFactorConfirmResponse;
use Laravel\Fortify\Contracts\FailedTwoFactorLoginResponse;
use Laravel\Fortify\Contracts\TwoFactorChallengeViewResponse;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse;
use Laravel\Fortify\Contracts\TwoFactorSetupEnforcedViewResponse;
use Laravel\Fortify\Events\RecoveryCodeReplaced;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Requests\TwoFactorEnforcedSetupRequest;
use Laravel\Fortify\Http\Requests\TwoFactorLoginRequest;

class TwoFactorAuthenticatedSessionController extends Controller
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
     * Show the two factor authentication challenge view.
     *
     * @param  \Laravel\Fortify\Http\Requests\TwoFactorLoginRequest  $request
     * @return \Laravel\Fortify\Contracts\TwoFactorChallengeViewResponse
     */
    public function create(TwoFactorLoginRequest $request): TwoFactorChallengeViewResponse
    {
        if (! $request->hasChallengedUser()) {
            throw new HttpResponseException(redirect()->route('login'));
        }

        return app(TwoFactorChallengeViewResponse::class);
    }

    /**
     * Show the two factor authentication setup view.
     *
     * @param  \Laravel\Fortify\Http\Requests\TwoFactorEnforcedSetupRequest  $request
     * @return \Laravel\Fortify\Contracts\TwoFactorSetupEnforcedViewResponse
     */
    public function setup(TwoFactorEnforcedSetupRequest $request)
    {
        return app(TwoFactorSetupEnforcedViewResponse::class)->toResponse($request);
    }

    /**
     * Complete the two factor authentication setup process.
     *
     * @param  \Laravel\Fortify\Http\Requests\TwoFactorEnforcedSetupRequest  $request
     * @return \Laravel\Fortify\Contracts\TwoFactorLoginResponse
     */
    public function completeSetup(TwoFactorEnforcedSetupRequest $request)
    {
        $user = $request->setupUser();

        if (Fortify::confirmsTwoFactorAuthentication()) {
            try {
                app(ConfirmTwoFactorAuthentication::class)($user, $request->code);
            } catch (ValidationException $e) {
                return app(FailedTwoFactorConfirmResponse::class)->toResponse($request, [
                    'errors' => $e->errorBag('confirmTwoFactorAuthentication'),
                ]);
            }
        }

        $this->guard->login($user, $request->remember());

        $request->session()->forget('login.id');
        $request->session()->regenerate();

        return app(TwoFactorLoginResponse::class);
    }

    /**
     * Attempt to authenticate a new session using the two factor authentication code.
     *
     * @param  \Laravel\Fortify\Http\Requests\TwoFactorLoginRequest  $request
     * @return mixed
     */
    public function store(TwoFactorLoginRequest $request)
    {
        $user = $request->challengedUser();

        if ($code = $request->validRecoveryCode()) {
            $user->replaceRecoveryCode($code);

            event(new RecoveryCodeReplaced($user, $code));
        } elseif (! $request->hasValidCode()) {
            return app(FailedTwoFactorLoginResponse::class)->toResponse($request);
        }

        $this->guard->login($user, $request->remember());

        $request->session()->regenerate();

        return app(TwoFactorLoginResponse::class);
    }
}
