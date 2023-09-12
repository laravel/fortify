<?php

namespace Laravel\Fortify\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\SuccessfulEmailVerificationNotificationResponse as SuccessfulEmailVerificationNotificationResponseContract;
use Laravel\Fortify\Fortify;

class SuccessfulEmailVerificationNotificationResponse implements SuccessfulEmailVerificationNotificationResponseContract
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
                    ? new JsonResponse('', 202)
                    : back()->with('status', Fortify::VERIFICATION_LINK_SENT);
    }
}
