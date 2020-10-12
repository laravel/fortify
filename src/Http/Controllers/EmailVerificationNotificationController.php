<?php

namespace Laravel\Fortify\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

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
                        : redirect()->intended(config('fortify.home'));
        }

        $request->user()->sendEmailVerificationNotification();

        return $request->wantsJson()
                    ? new JsonResponse('', 202)
                    : back()->with('status', 'verification-link-sent');
    }
}
