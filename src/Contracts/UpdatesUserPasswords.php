<?php

namespace Laravel\Fortify\Contracts;

/**
 * @template TUser of \Illuminate\Database\Eloquent\Model = \Illuminate\Foundation\Auth\User
 *
 * @method void update(TUser $user, array $input)
 */
interface UpdatesUserPasswords
{
    //
}
