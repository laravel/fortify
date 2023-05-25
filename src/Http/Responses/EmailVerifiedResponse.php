<?php

namespace Laravel\Fortify\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\EmailVerifiedResponse as EmailVerifiedResponseContract;
use Laravel\Fortify\Fortify;

class EmailVerifiedResponse implements EmailVerifiedResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        return $request->wantsJson()
                    ? new JsonResponse('', 204)
                    : redirect()->intended(Fortify::redirects('email-verification'));
    }
}
