<?php

namespace Laravel\Fortify\Http\Responses;

use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\FailedTwoFactorLoginResponse as FailedTwoFactorLoginResponseContract;

class FailedTwoFactorLoginResponse implements FailedTwoFactorLoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        if ($request->filled('code')) {
            $message = __('The provided two factor authentication code was invalid.');
        } elseif ($request->filled('recovery_code')) {
            $message = __('The provided recovery code was invalid.');
        }

        if ($request->wantsJson()) {
            throw ValidationException::withMessages([
                'code' => [$message],
            ]);
        }

        return redirect()->route('two-factor.login')->withErrors(['code' => $message]);
    }
}
