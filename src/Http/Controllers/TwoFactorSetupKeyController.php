<?php

namespace Laravel\Fortify\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TwoFactorSetupKeyController extends Controller
{
    /**
     * Get the setup key for the user's two factor authentication.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(Request $request)
    {
        if (is_null($request->user()->two_factor_secret)) {
            return [];
        }

        return response()->json([
            'setupKey' => decrypt($request->user()->two_factor_secret),
        ]);
    }
}
