<?php

namespace Laravel\Fortify\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;

class TwoFactorAuthenticationController extends Controller
{
    /**
     * Enable two factor authentication for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Laravel\Fortify\Actions\EnableTwoFactorAuthentication  $enable
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(Request $request, EnableTwoFactorAuthentication $enable)
    {
        $enable($request->user());

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
