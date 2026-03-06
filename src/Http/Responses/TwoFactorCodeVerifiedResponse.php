<?php

namespace Laravel\Fortify\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\TwoFactorCodeVerifiedResponse as TwoFactorCodeVerifiedResponseContract;
use Laravel\Fortify\Fortify;

class TwoFactorCodeVerifiedResponse implements TwoFactorCodeVerifiedResponseContract
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
            : redirect()->intended(Fortify::redirects('two-factor-code-verified'));
    }
}
