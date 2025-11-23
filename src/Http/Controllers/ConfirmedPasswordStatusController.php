<?php

namespace Laravel\Fortify\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Date;
use Laravel\Fortify\Contracts\ConfirmedPasswordStatusResponse;

class ConfirmedPasswordStatusController extends Controller
{
    /**
     * Get the password confirmation status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Laravel\Fortify\Contracts\ConfirmedPasswordStatusResponse
     */
    public function show(Request $request)
    {
        $lastConfirmation = $request->session()->get(
            'auth.password_confirmed_at', 0
        );

        $lastConfirmed = (Date::now()->unix() - $lastConfirmation);

        $confirmed = $lastConfirmed < $request->input(
            'seconds', config('auth.password_timeout', 900)
        );

        return app(ConfirmedPasswordStatusResponse::class, [
            'confirmed' => $confirmed,
            'lastConfirmed' => $lastConfirmed
        ]);
    }
}
