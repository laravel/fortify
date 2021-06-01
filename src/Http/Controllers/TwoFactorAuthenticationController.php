<?php

namespace Laravel\Fortify\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravel\Fortify\Actions\GenerateTwoFactorAuthenticationSecret;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\ConfirmEnableTwoFactorAuthentication;

class TwoFactorAuthenticationController extends Controller
{
    /**
     * Enable two factor authentication for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Laravel\Fortify\Actions\GenerateTwoFactorAuthenticationSecret  $enable
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(Request $request, GenerateTwoFactorAuthenticationSecret $generate)
    {
        $generate($request->user());

        return $request->wantsJson()
                    ? new JsonResponse('', 200)
                    : back()->with('status', 'two-factor-authentication-enabled');
    }

    /**
     * Confirms activation of two-factor authentication by validating a TOTP code
     * @param  \Illuminate\Http\Request  $request
     * @param \Laravel\Fortify\Actions\ConfirmEnableTwoFactorAuthentication $confirm
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function confirm(Request $request, ConfirmEnableTwoFactorAuthentication $enable)
    {
        if (!$enable($request->user(), $request->code)) {
            $error = __('The provided two factor authentication code was invalid.');
            return $request->wantsJson()
                ? new JsonResponse($error, 422)
                : back()->withErrors($error);
        }

        return $request->wantsJson()
            ? new JsonResponse('', 200)
            : back();
    }

    /**
     * Disable two factor authentication for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Laravel\Fortify\Actions\DisableTwoFactorAuthentication  $disable
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function destroy(Request $request, DisableTwoFactorAuthentication $disable)
    {
        $disable($request->user());

        return $request->wantsJson()
                    ? new JsonResponse('', 200)
                    : back()->with('status', 'two-factor-authentication-disabled');
    }
}
