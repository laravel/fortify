<?php

namespace Laravel\Fortify\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TwoFactorQrCodeController extends Controller
{
    /**
     * Get the SVG element for the user's two factor authentication QR code.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(Request $request)
    {
        return response()->json(['svg' => $request->user()->twoFactorQrCodeSvg()]);
    }
}
