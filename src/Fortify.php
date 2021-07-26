<?php

namespace Laravel\Fortify;

use Laravel\Fortify\Contracts\ConfirmPasswordViewResponse;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Fortify\Contracts\LoginViewResponse;
use Laravel\Fortify\Contracts\RegisterViewResponse;
use Laravel\Fortify\Contracts\RequestPasswordResetLinkViewResponse;
use Laravel\Fortify\Contracts\ResetPasswordViewResponse;
use Laravel\Fortify\Contracts\ResetsUserPasswords;
use Laravel\Fortify\Contracts\TwoFactorChallengeViewResponse;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use Laravel\Fortify\Contracts\VerifyEmailViewResponse;
use Laravel\Fortify\Http\Responses\SimpleViewResponse;

class Fortify
{
    /**
     * The callback that is responsible for building the authentication pipeline array, if applicable.
     *
     * @var callable|null
     */
    public static $authenticateThroughCallback;

    /**
     * The callback that is responsible for validating authentication credentials, if applicable.
     *
     * @var callable|null
     */
    public static $authenticateUsingCallback;

    /**
     * The callback that is responsible for confirming user passwords.
     *
     * @var callable|null
     */
    public static $confirmPasswordsUsingCallback;

    /**
     * Indicates if Fortify routes will be registered.
     *
     * @var bool
     */
    public static $registersRoutes = true;

    /**
     * Get the username used for authentication.
     *
     * @return string
     */
    public static function username()
    {
        return config('fortify.username', 'email');
    }

    /**
     * Get the name of the email address request variable / field.
     *
     * @return string
     */
    public static function email()
    {
        return config('fortify.email', 'email');
    }

    /**
     * Get a completion redirect path for a specific feature.
     *
     * @param  string  $redirect
     * @return string
     */
    public static function redirects(string $redirect, $default = null)
    {
        return config('fortify.redirects.'.$redirect) ?? $default ?? config('fortify.home');
    }

    /**
     * Register the views for Fortify using conventional names under the given namespace.
     *
     * @param  string  $namespace
     * @return void
     */
    public static function viewNamespace(string $namespace)
    {
        static::viewPrefix($namespace.'::');
    }

    /**
     * Register the views for Fortify using conventional names under the given prefix.
     *
     * @param  string  $prefix
     * @return void
     */
    public static function viewPrefix(string $prefix)
    {
        static::loginView($prefix.'login');
        static::twoFactorChallengeView($prefix.'two-factor-challenge');
        static::registerView($prefix.'register');
        static::requestPasswordResetLinkView($prefix.'forgot-password');
        static::resetPasswordView($prefix.'reset-password');
        static::verifyEmailView($prefix.'verify-email');
        static::confirmPasswordView($prefix.'confirm-password');
    }

    /**
     * Specify which view should be used as the login view.
     *
     * @param  callable|string  $view
     * @return void
     */
    public static function loginView($view)
    {
        app()->singleton(LoginViewResponse::class, function () use ($view) {
            return new SimpleViewResponse($view);
        });
    }

    /**
     * Specify which view should be used as the two factor authentication challenge view.
     *
     * @param  callable|string  $view
     * @return void
     */
    public static function twoFactorChallengeView($view)
    {
        app()->singleton(TwoFactorChallengeViewResponse::class, function () use ($view) {
            return new SimpleViewResponse($view);
        });
    }

    /**
     * Specify which view should be used as the new password view.
     *
     * @param  callable|string  $view
     * @return void
     */
    public static function resetPasswordView($view)
    {
        app()->singleton(ResetPasswordViewResponse::class, function () use ($view) {
            return new SimpleViewResponse($view);
        });
    }

    /**
     * Specify which view should be used as the registration view.
     *
     * @param  callable|string  $view
     * @return void
     */
    public static function registerView($view)
    {
        app()->singleton(RegisterViewResponse::class, function () use ($view) {
            return new SimpleViewResponse($view);
        });
    }

    /**
     * Specify which view should be used as the email verification prompt.
     *
     * @param  callable|string  $view
     * @return void
     */
    public static function verifyEmailView($view)
    {
        app()->singleton(VerifyEmailViewResponse::class, function () use ($view) {
            return new SimpleViewResponse($view);
        });
    }

    /**
     * Specify which view should be used as the password confirmation prompt.
     *
     * @param  callable|string  $view
     * @return void
     */
    public static function confirmPasswordView($view)
    {
        app()->singleton(ConfirmPasswordViewResponse::class, function () use ($view) {
            return new SimpleViewResponse($view);
        });
    }

    /**
     * Specify which view should be used as the request password reset link view.
     *
     * @param  callable|string  $view
     * @return void
     */
    public static function requestPasswordResetLinkView($view)
    {
        app()->singleton(RequestPasswordResetLinkViewResponse::class, function () use ($view) {
            return new SimpleViewResponse($view);
        });
    }

    /**
     * Register a callback that is responsible for building the authentication pipeline array.
     *
     * @param  callable  $callback
     * @return void
     */
    public static function loginThrough(callable $callback)
    {
        static::authenticateThrough($callback);
    }

    /**
     * Register a callback that is responsible for building the authentication pipeline array.
     *
     * @param  callable  $callback
     * @return void
     */
    public static function authenticateThrough(callable $callback)
    {
        static::$authenticateThroughCallback = $callback;
    }

    /**
     * Register a callback that is responsible for validating incoming authentication credentials.
     *
     * @param  callable  $callback
     * @return void
     */
    public static function authenticateUsing(callable $callback)
    {
        static::$authenticateUsingCallback = $callback;
    }

    /**
     * Register a callback that is responsible for confirming existing user passwords as valid.
     *
     * @param  callable  $callback
     * @return void
     */
    public static function confirmPasswordsUsing(callable $callback)
    {
        static::$confirmPasswordsUsingCallback = $callback;
    }

    /**
     * Register a class / callback that should be used to create new users.
     *
     * @param  string  $callback
     * @return void
     */
    public static function createUsersUsing(string $callback)
    {
        app()->singleton(CreatesNewUsers::class, $callback);
    }

    /**
     * Register a class / callback that should be used to update user profile information.
     *
     * @param  string  $callback
     * @return void
     */
    public static function updateUserProfileInformationUsing(string $callback)
    {
        app()->singleton(UpdatesUserProfileInformation::class, $callback);
    }

    /**
     * Register a class / callback that should be used to update user passwords.
     *
     * @param  string  $callback
     * @return void
     */
    public static function updateUserPasswordsUsing(string $callback)
    {
        app()->singleton(UpdatesUserPasswords::class, $callback);
    }

    /**
     * Register a class / callback that should be used to reset user passwords.
     *
     * @param  string  $callback
     * @return void
     */
    public static function resetUserPasswordsUsing(string $callback)
    {
        app()->singleton(ResetsUserPasswords::class, $callback);
    }

    /**
     * Configure Fortify to not register its routes.
     *
     * @return static
     */
    public static function ignoreRoutes()
    {
        static::$registersRoutes = false;

        return new static;
    }
}
