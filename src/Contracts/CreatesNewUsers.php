<?php

namespace Laravel\Fortify\Contracts;

use App\Http\Requests\UserRegistrationRequest;

interface CreatesNewUsers
{
    /**
     * Validate and create a newly registered user.
     *
     * @param  \App\Http\Requests\UserRegistrationRequest  $request
     * @return mixed
     */
    public function create(UserRegistrationRequest $request);
}
