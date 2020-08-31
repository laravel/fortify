<?php

namespace Laravel\Fortify\Actions;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Str;

class CompletePasswordReset
{
    /**
     * Complete the password reset process for the given user.
     *
     * @param  \Illuminate\Contracts\Auth\StatefulGuard  $guard
     * @param  mixed  $user
     * @return void
     */
    public function __invoke(StatefulGuard $guard, $user)
    {
        $user->setRememberToken(Str::random(60));

        $user->save();

        event(new PasswordReset($user));

        // $guard->login($user);
    }
}
