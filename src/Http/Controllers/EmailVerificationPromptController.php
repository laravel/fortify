<?php

namespace Laravel\Fortify\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravel\Fortify\Contracts\VerifyEmailViewResponse;
use Laravel\Fortify\Http\Responses\RedirectAsIntended;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Laravel\Fortify\Contracts\VerifyEmailViewResponse
     */
    public function __invoke(Request $request)
    {
        return $request->user()->hasVerifiedEmail()
            ? app(RedirectAsIntended::class, ['name' => 'email-verification'])
            : app(VerifyEmailViewResponse::class);
    }
}
