<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\RedirectAfterRegister as RedirectAfterRegisterInterface;

class RedirectAfterRegister implements RedirectAfterRegisterInterface
{
    public function afterRegister($guard, $user){
        $guard->login($user);
    }
}
