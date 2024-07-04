<?php

namespace Laravel\Fortify\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravel\Fortify\Contracts\EmailVerificationNotificationSentResponse;
use Laravel\Fortify\Http\Responses\RedirectAsIntended;

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
            return $request->wantsJson()
                        ? new JsonResponse('', 204)
                        : app(RedirectAsIntended::class, ['name' => 'email-verification']);
        }

        $request->user()->sendEmailVerificationNotification();

        return app(EmailVerificationNotificationSentResponse::class);
    }
}
