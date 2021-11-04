<?php

namespace Laravel\Fortify\Actions;

use Illuminate\Contracts\Auth\StatefulGuard;
use Laravel\Fortify\Events\RecoveryCodeReplaced;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Requests\TwoFactorConfirmationRequest;
use Laravel\Fortify\Http\Responses\FailedTwoFactorConfirmationRecoveryResponse;
use Laravel\Fortify\Http\Responses\FailedTwoFactorConfirmationResponse;
use Laravel\Fortify\Http\Responses\TwoFactorConfirmedResponse;

class ConfirmTwoFactor
{
    /**
     * Confirm that the given two factor is valid for the given user.
     *
     * @param  \Illuminate\Contracts\Auth\StatefulGuard  $guard
     * @param  \Laravel\Fortify\Http\Requests\TwoFactorConfirmationRequest  $request
     * @param  mixed  $user
     * @return bool
     */
    public function __invoke(StatefulGuard $guard, TwoFactorConfirmationRequest $request, $user)
    {
        if (is_null(Fortify::$confirmTwoFactorsUsingCallback)) {
            if ($request->filled('recovery_code')) {
                $code = $request->validRecoveryCode();

                if (! $code) {
                    return app(FailedTwoFactorConfirmationRecoveryResponse::class);
                }

                $user->replaceRecoveryCode($code);
                event(new RecoveryCodeReplaced($user, $code));
            } elseif (! $request->hasValidCode()) {
                return app(FailedTwoFactorConfirmationResponse::class);
            }

            return app(TwoFactorConfirmedResponse::class);
        } else {
            return $this->confirmTwoFactorUsingCustomCallback($user, $request);
        }
    }

    /**
     * Confirm the user's two factor using a custom callback.
     *
     * @param  \Laravel\Fortify\Http\Requests\TwoFactorConfirmationRequest  $request
     * @param  mixed  $user
     * @return bool
     */
    protected function confirmTwoFactorUsingCustomCallback(TwoFactorConfirmationRequest $request, $user)
    {
        return call_user_func(
            Fortify::$confirmTwoFactorsUsingCallback,
            $user,
            $request
        );
    }
}
