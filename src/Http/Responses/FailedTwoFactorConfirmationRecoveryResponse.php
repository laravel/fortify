<?php

namespace Laravel\Fortify\Http\Responses;

use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\FailedTwoFactorConfirmationRecoveryResponse as FailedTwoFactorConfirmationRecoveryResponseContract;

class FailedTwoFactorConfirmationRecoveryResponse implements FailedTwoFactorConfirmationRecoveryResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        $message = __('The provided two factor recovery code was invalid.');

        if ($request->wantsJson()) {
            throw ValidationException::withMessages([
                'recovery_code' => [$message],
            ]);
        }

        return back()->withErrors(['recovery_code' => $message]);
    }
}
