<?php

use Laravel\Fortify\Features;

return [
	'guard' => 'web',
	'middleware' => ['web'],
	'passwords' => 'users',
	'email-verification-model' => App\Models\User::class,
	'username' => 'email',
	'email' => 'email',
	'views' => true,
	'home' => '/home',
	'prefix' => '',
	'domain' => null,
	'limiters' => [
		'login' => null,
		'two-factor' => null,
	],
	'redirects' => [
		'login' => null,
		'logout' => null,
		'password-confirmation' => null,
		'register' => null,
		'email-verification' => null,
		'password-reset' => null,
	],
	'features' => [
		Features::registration(),
		Features::resetPasswords(),
		Features::emailVerification(),
		Features::updateProfileInformation(),
		Features::updatePasswords(),
		Features::twoFactorAuthentication(),
	],
];
