<?php

namespace Laravel\Fortify\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravel\Fortify\Contracts\TwoFactorQrCodeResponse;

class TwoFactorQrCodeController extends Controller
{
    /**
     * Get the SVG element for the user's two factor authentication QR code.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Laravel\Fortify\Contracts\TwoFactorQrCodeResponse
     */
    public function show(Request $request)
    {
        if (! $request->user()->two_factor_secret) {
            abort(404, 'Two factor authentication has not been enabled.');
        }

        return app(TwoFactorQrCodeResponse::class);
    }
}
