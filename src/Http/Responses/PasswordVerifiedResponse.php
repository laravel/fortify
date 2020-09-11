<?php

namespace Laravel\Fortify\Http\Responses;

use Illuminate\Http\Response;
use Laravel\Fortify\Contracts\PasswordVerifiedResponse as PasswordVerifiedResponseContract;

class PasswordVerifiedResponse implements PasswordVerifiedResponseContract
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
                    ? new Response('', 201)
                    : redirect()->intended(config('fortify.home'));
    }
}
