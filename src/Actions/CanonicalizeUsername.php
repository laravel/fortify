<?php

namespace Laravel\Fortify\Actions;

use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class CanonicalizeUsername
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  callable  $next
     * @return mixed
     */
    public function handle($request, $next)
    {
        if ($request->has(Fortify::username())) {
            $request->merge([
                Fortify::username() => Str::lower($request->{Fortify::username()}),
            ]);
        }

        return $next($request);
    }
}
