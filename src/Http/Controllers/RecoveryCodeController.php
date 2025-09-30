<?php

namespace Laravel\Fortify\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Laravel\Fortify\Contracts\RecoveryCodesGeneratedResponse;
use Laravel\Fortify\Fortify;

class RecoveryCodeController extends Controller
{
    /**
     * Get the two factor authentication recovery codes for authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (! $request->user()->two_factor_secret ||
            ! $request->user()->two_factor_recovery_codes) {
            return [];
        }

        return response()->json(json_decode(Fortify::currentEncrypter()->decrypt(
            $request->user()->two_factor_recovery_codes
        ), true));
    }

    /**
     * Generate a fresh set of two factor authentication recovery codes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Laravel\Fortify\Actions\GenerateNewRecoveryCodes  $generate
     * @return \Laravel\Fortify\Contracts\RecoveryCodesGeneratedResponse
     */
    public function store(Request $request, GenerateNewRecoveryCodes $generate)
    {
        $generate($request->user());

        return app(RecoveryCodesGeneratedResponse::class);
    }
}
