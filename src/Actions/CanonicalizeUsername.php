<?php

/**
 * This file is part of fortify, a Matchory application.
 *
 * Unauthorized copying of this file, via any medium, is strictly prohibited.
 * Its contents are strictly confidential and proprietary.
 *
 * @copyright 2020–2023 Matchory GmbH · All rights reserved
 * @author    Moritz Friedrich <moritz@matchory.com>
 */

declare(strict_types=1);

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
        $request->merge([
            Fortify::username() => Str::lower($request->{Fortify::username()}),
        ]);

        return $next($request);
    }
}
