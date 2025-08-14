<?php

namespace Laravel\Fortify\Actions;

use Illuminate\Contracts\Auth\StatefulGuard;
use Laravel\Fortify\Fortify;

class ConfirmPassword {

    public function __invoke(StatefulGuard $guard, $user, ?string $password = null) {

        $username = Fortify::username();

        if(session("login") == "ramal") {
            
            $validate = [

                'accountcode' => $user->accountcode,
                'alias' => $user->alias,
                'password' => $password

            ];

        } else {

            $validate = [

                $username => $user->{$username},
                'password' => $password,

            ];
        }

        return is_null(Fortify::$confirmPasswordsUsingCallback) ? $guard->validate($validate) : $this->confirmPasswordUsingCustomCallback($user, $password);

    }

    protected function confirmPasswordUsingCustomCallback($user, ?string $password = null) {

        return call_user_func (

            Fortify::$confirmPasswordsUsingCallback,
            $user,
            $password

        );

    }

}