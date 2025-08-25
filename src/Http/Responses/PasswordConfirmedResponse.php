<?php

namespace Laravel\Fortify\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\PasswordConfirmedResponse as PasswordConfirmedResponseContract;
use Laravel\Fortify\Fortify;

class PasswordConfirmedResponse implements PasswordConfirmedResponseContract {

    public function toResponse($request) {

        if(session("login") == "ramal") {

            $route = redirect()->route("login.2fa");

        } else {

            $route = redirect()->route("home");

        }

        return $request->wantsJson()

            ? new JsonResponse('', 201)
            : $route;

    }
}
