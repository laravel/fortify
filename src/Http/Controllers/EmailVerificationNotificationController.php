<?php

namespace Laravel\Fortify\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravel\Fortify\Contracts\EmailVerificationLinkSentResponse;
use Laravel\Fortify\Contracts\EmailVerifiedResponse;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return app(EmailVerifiedResponse::class);
        }

        $request->user()->sendEmailVerificationNotification();

        return app(EmailVerificationLinkSentResponse::class);
    }
}
