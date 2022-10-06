<?php

namespace Laravel\Fortify\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Fortify;

class ConfirmedTwoFactorAuthenticationController extends Controller
{
    /**
     * Enable two factor authentication for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication  $confirm
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(Request $request, ConfirmTwoFactorAuthentication $confirm)
    {
        $confirm($request->user(), $request->input('code'));

        return $request->wantsJson()
                    ? new JsonResponse('', 200)
                    : back()->with('status', Fortify::TWO_FACTOR_AUTHENTICATION_CONFIRMED);
    }
}
