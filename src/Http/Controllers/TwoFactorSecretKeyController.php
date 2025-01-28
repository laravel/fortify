<?php

namespace Laravel\Fortify\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TwoFactorSecretKeyController extends Controller
{
    /**
     * Get the current user's two factor authentication setup / secret key.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(Request $request)
    {
        if (is_null($request->user()->two_factor_secret)) {
            abort(404, 'Two factor authentication has not been enabled.');
        }

        return response()->json([
            'secretKey' => decrypt($request->user()->two_factor_secret),
        ]);
    }
}
