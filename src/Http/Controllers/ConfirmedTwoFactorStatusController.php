<?php

namespace Laravel\Fortify\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ConfirmedTwoFactorStatusController extends Controller
{
    /**
     * Get the two factor confirmation status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        return response()->json([
            'confirmed' => (time() - $request->session()->get('auth.two_factor_confirmed_at', 0)) < $request->input('seconds', config('auth.two_factor_timeout', 900)),
        ]);
    }
}
