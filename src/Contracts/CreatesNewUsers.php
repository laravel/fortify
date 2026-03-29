<?php

namespace Laravel\Fortify\Contracts;

/**
 * @template TUser of \Illuminate\Database\Eloquent\Model = \Illuminate\Foundation\Auth\User
 */
interface CreatesNewUsers
{
    /**
     * Validate and create a newly registered user.
     *
     * @param  array  $input
     * @return TUser
     */
    public function create(array $input);
}
