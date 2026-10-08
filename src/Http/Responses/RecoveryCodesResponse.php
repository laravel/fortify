<?php

namespace Laravel\Fortify\Http\Responses;

use Laravel\Fortify\Contracts\RecoveryCodesResponse as RecoveryCodesResponseContract;
use Laravel\Fortify\Fortify;

class RecoveryCodesResponse implements RecoveryCodesResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        return response()->json(json_decode(Fortify::currentEncrypter()->decrypt(
            $request->user()->two_factor_recovery_codes
        ), true));
    }
}
