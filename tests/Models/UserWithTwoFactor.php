<?php

namespace Laravel\Fortify\Tests\Models;

use Laravel\Fortify\TwoFactorAuthenticatable;

class UserWithTwoFactor extends \Illuminate\Foundation\Auth\User
{
    use TwoFactorAuthenticatable;

    protected $table = 'users';
}
