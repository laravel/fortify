<?php

namespace Laravel\Fortify\Contracts;

/**
 * @template TUser of \Illuminate\Database\Eloquent\Model = \Illuminate\Foundation\Auth\User
 *
 * @method void reset(TUser $user, array $input)
 */
interface ResetsUserPasswords
{
    //
}
