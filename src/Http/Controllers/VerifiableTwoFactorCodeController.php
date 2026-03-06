<?php

namespace Laravel\Fortify\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Date;
use Laravel\Fortify\Actions\VerifyTwoFactorCode;
use Laravel\Fortify\Contracts\FailedTwoFactorCodeVerificationResponse;
use Laravel\Fortify\Contracts\TwoFactorCodeVerifiedResponse;
use Laravel\Fortify\Contracts\VerifyTwoFactorCodeViewResponse;

class VerifiableTwoFactorCodeController extends Controller
{
    /**
     * Show the two factor code verification view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Laravel\Fortify\Contracts\VerifyTwoFactorCodeViewResponse
     */
    public function show(Request $request)
    {
        return app(VerifyTwoFactorCodeViewResponse::class);
    }

    /**
     * Verify the user's two factor authentication code.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Laravel\Fortify\Actions\VerifyTwoFactorCode  $verify
     * @return \Illuminate\Contracts\Support\Responsable
     */
    public function store(Request $request, VerifyTwoFactorCode $verify)
    {
        $verified = $verify($request->user(), $request->input('code'));

        if ($verified) {
            $request->session()->put('auth.two_factor_confirmed_at', Date::now()->unix());
        }

        return $verified
            ? app(TwoFactorCodeVerifiedResponse::class)
            : app(FailedTwoFactorCodeVerificationResponse::class);
    }
}
