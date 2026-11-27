<?php

namespace Laravel\Fortify\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Passkeys\Contracts\PasskeyLoginResponse;
use Symfony\Component\HttpFoundation\Response;

class SkipPasskeyLoginIfAuthenticated
{
    /**
     * Answer an already authenticated login request the way a successful
     * ceremony would.
     *
     * The passkey login routes are consumed by the passkeys JavaScript
     * client, which sends an "Accept: application/json" header and parses
     * the response as JSON. The "guest" middleware redirects authenticated
     * requests to an HTML page, which fetch follows and the client cannot
     * parse. A login tab that outlived its session (signed in from another
     * tab, or recalled through a remember-me cookie) therefore failed with a
     * JSON parse error on every attempt instead of simply landing on the page
     * it was sent to.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard(config('fortify.guard'))->check()) {
            return $next($request);
        }

        return app(PasskeyLoginResponse::class)->toResponse($request);
    }
}
