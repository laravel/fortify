<?php


namespace Laravel\Fortify\Contracts;


interface RedirectAfterRegister
{
    /**
     * Action after create user.
     *
     * @param  mixed $user
     * @return mixed
     */
    public function afterRegister($guard, $user);

}
