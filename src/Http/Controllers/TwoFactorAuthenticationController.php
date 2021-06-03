<?php

namespace Laravel\Fortify\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateTwoFactorAuthenticationSecret;
use Laravel\Fortify\Contracts\FailedTwoFactorEnableResponse;

class TwoFactorAuthenticationController extends Controller
{
    /**
     * Generate two factor authentication secret and recovery codes for the user.
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
                    : back()->with('status', 'two-factor-authentication-secret-generated');
    }

    /**
     * Confirms activation of two-factor authentication by validating a TOTP code.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param \Laravel\Fortify\Actions\EnableTwoFactorAuthentication $confirm
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function confirm(Request $request, EnableTwoFactorAuthentication $enable)
    {
        if (! $enable($request->user(), $request->code)) {
            return app(FailedTwoFactorEnableResponse::class);
        }

        return $request->wantsJson()
            ? new JsonResponse('', 200)
            : back()->with('status', 'two-factor-authentication-enabled');
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
