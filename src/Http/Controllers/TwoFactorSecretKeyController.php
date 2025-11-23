<?php

namespace Laravel\Fortify\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravel\Fortify\Contracts\TwoFactorSecretKeyResponse;
use Laravel\Fortify\Fortify;

class TwoFactorSecretKeyController extends Controller
{
    /**
     * Get the current user's two factor authentication setup / secret key.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Laravel\Fortify\Contracts\TwoFactorSecretKeyResponse
     */
    public function show(Request $request)
    {
        if (! $request->user()->two_factor_secret) {
            abort(404, 'Two factor authentication has not been enabled.');
        }

        return app(TwoFactorSecretKeyResponse::class, [
            'secretKey' => Fortify::currentEncrypter()->decrypt($request->user()->two_factor_secret),
        ]);
    }
}
