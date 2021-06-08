<?php

namespace Laravel\Fortify\Contracts;

use Illuminate\Http\Request;

interface CreatesNewUsers
{
    /**
     * Validate and create a newly registered user.
     *
     * @param  array  $input
     * @return mixed
     */
    public function create(Request $request);
}
