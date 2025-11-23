<?php

namespace Laravel\Fortify\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\TwoFactorQrCodeResponse as TwoFactorQrCodeResponseContract;

class TwoFactorQrCodeResponse implements TwoFactorQrCodeResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        return new JsonResponse([
            'svg' => $request->user()->twoFactorQrCodeSvg(),
            'url' => $request->user()->twoFactorQrCodeUrl(),
        ]);
    }
}
