<?php

namespace Laravel\Fortify\Tests\Models;

use Laravel\Fortify\TwoFactorAuthenticatable;

class UserWithTwoFactor extends User
{
    use TwoFactorAuthenticatable;

    protected $table = 'users';
}
