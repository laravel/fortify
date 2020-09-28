<?php

namespace Laravel\Fortify\Contracts;

interface CreatesNewUsers
{
    /**
     * Validate and create a newly registered user.
     *
     * @param  array  $input
     * @return mixed
     */
    public function create(array $input);

    /**
     * The user has been created.
     *
     * @param  mixed  $user
     * @return mixed
     */
    public function created($user);
}
