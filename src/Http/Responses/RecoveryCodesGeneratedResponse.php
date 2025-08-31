<?php

namespace Laravel\Fortify\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\RecoveryCodesGeneratedResponse as RecoveryCodesGeneratedResponseContract;
use Laravel\Fortify\Fortify;

class RecoveryCodesGeneratedResponse implements RecoveryCodesGeneratedResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        return $request->wantsJson()
            ? new JsonResponse('', 200)
            : back()->with('status', Fortify::RECOVERY_CODES_GENERATED);
    }
}
