<?php

namespace Laravel\Fortify\Contracts;

interface CreatesNewUsers
{
    /**
     * Validate and create a newly registered user.
     *
     * @param  \Laravel\Fortify\Contracts\UserRegistrationRequest  $request
     * @return mixed
     */
    public function create(UserRegistrationRequest $request);
}
