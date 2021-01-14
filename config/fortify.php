<?php

use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;


return [
    'guard' => 'web',
    'middleware' => ['web'],
    'passwords' => 'users',
    'username' => 'email',
    'email' => 'email',
    'views' => true,
    'home' => '/home',
    'prefix' => '',
    'domain' => null,
    'limiters' => [
        'login' => null,
    ],
    'features' => [
        Features::registration(),
        Features::resetPasswords(),
        Features::emailVerification(),
        Features::updateProfileInformation(),
        Features::updatePasswords(),
        Features::twoFactorAuthentication(),
    ],
    'rules' => [
        'login' => [
            Fortify::username() => 'required|string',
            'password' => 'required|string',
        ],
        'twoFactorLogin' => [
            'code' => 'nullable|string',
            'recovery_code' => 'nullable|string',
        ],
    ],
];
