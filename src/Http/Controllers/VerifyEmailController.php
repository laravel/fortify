<?php

namespace Laravel\Fortify\Http\Controllers;

use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Laravel\Fortify\Http\Requests\VerifyEmailRequest;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     *
     * @param  \Laravel\Fortify\Http\Requests\VerifyEmailRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(VerifyEmailRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(config('fortify.home').'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended(config('fortify.home').'?verified=1');
    }
}
