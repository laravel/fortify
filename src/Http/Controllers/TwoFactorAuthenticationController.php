<?php

namespace Laravel\Fortify\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Contracts\TwoFactorDisabledResponse;
use Laravel\Fortify\Contracts\TwoFactorEnabledResponse;

class TwoFactorAuthenticationController extends Controller {

    public function store(Request $request, EnableTwoFactorAuthentication $enable) {

        $enable($request->user());

        session([

            "2fa" => "confirmed"

        ]);

        return app(TwoFactorEnabledResponse::class);

    }

    public function destroy(Request $request, DisableTwoFactorAuthentication $disable) {

        $disable($request->user());

        return app(TwoFactorDisabledResponse::class);

    }

}