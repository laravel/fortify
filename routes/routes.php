<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::group(['middleware' => config('fortify.middleware', ['web'])], function () {
    // Authentication...
    Route::get('/login', 'AuthenticatedSessionController@create')
                ->middleware(['guest'])
                ->name('login');

    $limiter = config('fortify.limiters.login');

    Route::post('/login', 'AuthenticatedSessionController@store')
                ->middleware(array_filter([
                    'guest',
                    $limiter ? 'throttle:'.$limiter : null,
                ]));

    Route::get('/two-factor-challenge', 'TwoFactorAuthenticatedSessionController@create')
                ->middleware(['guest'])
                ->name('two-factor.login');

    Route::post('/two-factor-challenge', 'TwoFactorAuthenticatedSessionController@store')
                ->middleware(['guest']);

    Route::post('/logout', 'AuthenticatedSessionController@destroy')
                ->name('logout');

    // Password Reset...
    if (Features::enabled(Features::resetPasswords())) {
        Route::get('/forgot-password', 'PasswordResetLinkController@create')
                    ->middleware(['guest'])
                    ->name('password.request');

        Route::post('/forgot-password', 'PasswordResetLinkController@store')
                    ->middleware(['guest'])
                    ->name('password.email');

        Route::get('/reset-password/{token}', 'NewPasswordController@create')
                    ->middleware(['guest'])
                    ->name('password.reset');

        Route::post('/reset-password', 'NewPasswordController@store')
                    ->middleware(['guest'])
                    ->name('password.update');
    }

    // Registration...
    if (Features::enabled(Features::registration())) {
        Route::get('/register', 'RegisteredUserController@create')
                    ->middleware(['guest'])
                    ->name('register');

        Route::post('/register', 'RegisteredUserController@store')
                    ->middleware(['guest']);
    }

    // Email Verification...
    if (Features::enabled(Features::emailVerification())) {
        Route::get('/email/verify', 'EmailVerificationPromptController')
                    ->middleware(['auth'])
                    ->name('verification.notice');

        Route::get('/email/verify/{id}/{hash}', 'VerifyEmailController')
                    ->middleware(['auth', 'signed', 'throttle:6,1'])
                    ->name('verification.verify');

        Route::post('/email/verification-notification', 'EmailVerificationNotificationController@store')
                    ->middleware(['auth', 'throttle:6,1'])
                    ->name('verification.send');
    }

    // Profile Information...
    if (Features::enabled(Features::updateProfileInformation())) {
        Route::put('/user/profile-information', 'ProfileInformationController@update')
                    ->middleware(['auth']);
    }

    // Passwords...
    if (Features::enabled(Features::updatePasswords())) {
        Route::put('/user/password', 'PasswordController@update')
                    ->middleware(['auth']);
    }

    // Password Confirmation...
    Route::get('/user/confirm-password', 'ConfirmablePasswordController@show')
                    ->middleware(['auth'])
                    ->name('password.confirm');

    Route::post('/user/confirm-password', 'ConfirmablePasswordController@store')
                    ->middleware(['auth']);

    Route::get('/user/confirmed-password-status', 'ConfirmedPasswordStatusController@show')
                    ->middleware(['auth'])
                    ->name('password.confirmation');

    // Two Factor Authentication...
    if (Features::enabled(Features::twoFactorAuthentication())) {
        $twoFactorMiddleware = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')
                        ? ['auth', 'password.confirm']
                        : ['auth'];

        Route::post('/user/two-factor-authentication', 'TwoFactorAuthenticationController@store')
                    ->middleware($twoFactorMiddleware);

        Route::delete('/user/two-factor-authentication', 'TwoFactorAuthenticationController@destroy')
                    ->middleware($twoFactorMiddleware);

        Route::get('/user/two-factor-qr-code', 'TwoFactorQrCodeController@show')
                    ->middleware($twoFactorMiddleware);

        Route::get('/user/two-factor-recovery-codes', 'RecoveryCodeController@index')
                    ->middleware($twoFactorMiddleware);

        Route::post('/user/two-factor-recovery-codes', 'RecoveryCodeController@store')
                    ->middleware($twoFactorMiddleware);
    }
});
