<p align="center"><img src="https://laravel.com/assets/img/components/logo-fortify.svg"></p>

<p align="center">
    <a href="https://github.com/laravel/fortify/actions">
        <img src="https://github.com/laravel/fortify/workflows/tests/badge.svg" alt="Build Status">
    </a>
    <a href="https://packagist.org/packages/laravel/fortify">
        <img src="https://img.shields.io/packagist/dt/laravel/fortify" alt="Total Downloads">
    </a>
    <a href="https://packagist.org/packages/laravel/fortify">
        <img src="https://img.shields.io/packagist/v/laravel/fortify" alt="Latest Stable Version">
    </a>
    <a href="https://packagist.org/packages/laravel/fortify">
        <img src="https://img.shields.io/packagist/l/laravel/fortify" alt="License">
    </a>
</p>

## Introduction

Laravel Fortify is a frontend agnostic authentication backend for Laravel. Fortify powers the registration, authentication, and two-factor authentication features of [Laravel Jetstream](https://github.com/laravel/jetstream).

- [Official Documentation](#official-documentation)
    - [Installation](#installation)
        - [The Fortify Service Provider](#the-fortify-service-provider)
        - [Fortify Features](#fortify-features)
        - [Disabling Views](#disabling-views)
    - [Authentication](#authentication)
        - [Customizing User Authentication](#customizing-user-authentication)
    - [Two Factor Authentication](#two-factor-authentication)
        - [Enabling Two Factor Authentication](#enabling-two-factor-authentication)
        - [Authenticating With Two Factor Authentication](#authenticating-with-two-factor-authentication)
        - [Disabling Two Factor Authentication](#disabling-two-factor-authentication)
    - [Registration](#registration)
        - [Customizing Registration](#customizing-registration)
    - [Password Reset](#password-reset)
        - [Requesting A Password Reset Link](#requesting-a-password-reset-link)
        - [Resetting The Password](#resetting-the-password)
        - [Customizing Password Resets](#customizing-password-resets)
    - [Email Verification](#email-verification)
        - [Protecting Routes](#protecting-routes)
    - [Password Confirmation](#password-confirmation)
- [Contributing](#contributing)
- [Code of Conduct](#code-of-conduct)
- [Security Vulnerabilities](#security-vulnerabilities)
- [License](#license)

## Official Documentation

> **Note:** Want an example of implementing each of these authentication related views? Check out their [Blade based Jetstream implementations](https://github.com/laravel/jetstream/tree/1.x/stubs/resources/views/auth)!

You may use Fortify (without Jetstream) to serve a headless authentication backend for your Laravel application. In this scenario, you are required to build your own templates using the frontend stack of your choice (Blade, Vue, etc.)

### Installation

To get started, install Fortify using Composer:

```bash
composer require laravel/fortify
```

Next, publish Fortify's resources:

```bash
php artisan vendor:publish --provider="Laravel\Fortify\FortifyServiceProvider"
```

This command will publish Fortify's actions to your `app/Actions` directory. This directory will be created if it does not exist. In addition, Fortify's configuration file and migrations will be published.

Next, you should migrate your database:

```bash
php artisan migrate
```

#### The Fortify Service Provider

The `vendor:publish` command discussed above will also publish the `app/Providers/FortifyServiceProvider` file. You should ensure this file is registered within the `providers` array of your `app` configuration file.

This service provider registers the actions that Fortify published, instructing Fortify to use them when their respective tasks are executed by Fortify.

#### Fortify Features

The `fortify` configuration file contains a `features` configuration array. This array defines which backend routes / features Fortify will expose by default. If you are not using Fortify in combination with [Laravel Jetstream](https://jetstream.laravel.com), we recommend that you only enable the following features, which is the same feature set available in previous Laravel authentication scaffolding packages:

```php
'features' => [
    Features::registration(),
    Features::resetPasswords(),
    Features::emailVerification(),
],
```

If you are not using Laravel Jetstream, you should implement user profile updates, password updates, and two-factor authentication yourself.

#### Disabling Views

By default, Fortify define routes that are intended to return views, such as a login screen or registration screen. However, if you are building a JavaScript driven single-page application, you may not have any need for these routes. For that reason, you may disable these routes entirely by setting the `views` configuration value within your `config/fortify.php` configuration file to `false`:

```php
'views' => false,
```

### Authentication

To get started, we need to instruct Fortify how to return our `login` view. Remember, Fortify is a headless authentication library. If you would like a frontend implementation of Fortify that is already completed for you, you should use [Laravel Jetstream](https://jetstream.laravel.com).

All of the authentication view's rendering logic may be customized using the appropriate methods available via the `Laravel\Fortify\Fortify` class. Typically, you should call this method from the `boot` method of your `FortifyServiceProvider`:

```php
use Laravel\Fortify\Fortify;

Fortify::loginView(function () {
    return view('auth.login');
});
```

Fortify will take care of generating the `/login` route that returns this view. Your `login` template should include a form that makes a POST request to `/login`. The `/login` action expects a string email address / username and a `password`. The name of the email / username field should match the `username` value of the `fortify` configuration file. In addition, a boolean `remember` field may be provided to indicate that the user would like to use the "remember me" functionality.

If the login attempt is successful, Fortify will redirect you to the URI configured via the `home` configuration option within your `fortify` configuration file. If the login request was an XHR request, a `200` HTTP response will be returned.

If the request was not successful, the user will be redirected back to the login screen and the validation errors will be available to you via the shared `$errors` Blade template variable. Or, in the case of an XHR request, the validation errors will be returned with the `422` HTTP response.

#### Customizing User Authentication

Fortify will automatically retrieve and authenticate the user based on the provided credentials and the authentication guard that is configured for your application. However, you may sometimes wish to have full customization over how login credentials are authenticated and users are retrieved. Thankfully, Fortify allows you to easily accomplish this using the `Fortify::authenticateUsing` method.

This method accepts a Closure which that receives the incoming HTTP request. The Closure is responsible for validating the login credentials attached to the request and returning the associated user instance. If the credentials are invalid or no user can be found, `null` or `false` should be returned by the Closure. Typically, this method should be called from the `boot` method of your `FortifyServiceProvider`:

```php
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Fortify;

Fortify::authenticateUsing(function (Request $request) {
    $user = User::where('email', $request->email)->first();

    if ($user &&
        Hash::check($request->password, $user->password)) {
        return $user;
    }
});
```

##### Authentication Guard

When customizing the authentication guard in your `fortify.php` file make sure that you're using an implementation of a `StatefulGuard` which Fortify needs in order to function properly. For example, Laravel's `api` guard uses stateless tokens so it cannot be used in combination with Fortify. If you are attempting to use Laravel Fortify to authenticate an SPA, you should use Laravel's default `web` guard in combination with [Laravel Sanctum](https://laravel.com/docs/sanctum).

### Two Factor Authentication

When two factor authentication is enabled, the user is required to input a six digit numeric token during the authentication process. This token is generated using a time-based one-time password (TOTP) that can be retrieved from any TOTP compatible mobile authentication application such as Google Authenticator.

To get started, you should first ensure that your application's `App\Models\User` model uses the `Laravel\Fortify\TwoFactorAuthenticatable` trait:

```php
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    use Notifiable, TwoFactorAuthenticatable;
}
 ```

Next, you should build a screen within your application where users can manage their two factor authentication settings. This screen should allow the user to enable and disable two factor authentication, as well as regenerate their two factor authentication recovery codes.

> By default, the `features` array of the `fortify` configuration file instructs Fortify's two factor authentication settings to require [password confirmation](#password-confirmation) before modification. Therefore, your application should implement Fortify's password confirmation feature before continuing.

#### Enabling Two Factor Authentication

To enable two factor authentication, your application should make a POST request to `/user/two-factor-authentication`. If the request is successful, the user will be redirected back to the previous URL and the `status` session variable will be set to `two-factor-authentication-enabled`. You may detect this `status` session variable within your templates to display the appropriate success message. If the request was an XHR request, `200` HTTP response will be returned:

```html
@if (session('status') == 'two-factor-authentication-enabled')
    <div class="mb-4 font-medium text-sm text-green-600">
        Two factor authentication has been enabled.
    </div>
@endif
```

Next, you should display the two factor authentication QR code for the user to scan into their authenticator application. If you are using Blade to render your application's frontend, you may retrieve the QR code SVG using the `twoFactorQrCodeSvg` method:

```php
$request->user()->twoFactorQrCodeSvg();
```

If you are building a JavaScript powered frontend, you may make an XHR GET request to `/user/two-factor-qr-code`. This URL will return a JSON object containing an `svg` key.

You should also display the user's two factor recovery codes. These recovery codes allow the user to authenticate if they lose access to their mobile device. If you are using Blade to render your application's frontend, you may access the recovery codes on the authenticated user:

```php
(array) $request->user()->two_factor_recovery_codes
```

If you are building a JavaScript powered frontend, you may make an XHR GET request to `/user/two-factor-recovery-codes`. This URL will return a JSON array containing the users recovery codes.

To regenerate the user's recovery codes, your application should make a POST request to `/user/two-factor-recovery-codes`.

#### Authenticating With Two Factor Authentication

During the authentication process, Fortify will automatically redirect the user to the two factor authentication challenge screen. However, if your application is making an XHR login request, the JSON response returned after a successful authentication attempt will contain a JSON object that has a `two_factor` boolean property. You should inspect this value to know whether you should redirect to your application's two factor authentication challenge screen.

To begin implementing two factor authentication functionality, we need to instruct Fortify how to return our "challenge" view. Remember, Fortify is a headless authentication library. If you would like a frontend implementation of Fortify that is already completed for you, you should use [Laravel Jetstream](https://jetstream.laravel.com/).

All of the authentication view's rendering logic may be customized using the appropriate methods available via the `Laravel\Fortify\Fortify` class. Typically, you should call this method from the `boot` method of your `FortifyServiceProvider`:

```php
use Laravel\Fortify\Fortify;

Fortify::twoFactorChallengeView(function () {
    return view('auth.two-factor-challenge');
});
```

Fortify will take care of generating the `/two-factor-challenge` route that returns this view. Your `two-factor-challenge` template should include a form that makes a POST request to `/two-factor-challenge`. The `/two-factor-challenge` action expects a `code` field that contains the user's current password, or a `recovery_code` field that contains one of the user's recovery codes.

If the login attempt is successful, Fortify will redirect you to the URI configured via the `home` configuration option within your `fortify` configuration file. If the login request was an XHR request, a `204` HTTP response will be returned.

If the request was not successful, the user will be redirected back to the login screen and the validation errors will be available to you via the shared `$errors` Blade template variable. Or, in the case of an XHR request, the validation errors will be returned with the `422` HTTP response.

#### Disabling Two Factor Authentication

To disable two factor authentication, your application should make a DELETE request to `/user/two-factor-authentication`. Remember, Fortify's two factor authentication endpoints require [password confirmation](#password-confirmation) prior to being called.

### Registration

To begin implementing registration functionality, we need to instruct Fortify how to return our `register` view. Remember, Fortify is a headless authentication library. If you would like a frontend implementation of Fortify that is already completed for you, you should use [Laravel Jetstream](https://jetstream.laravel.com).

All of the authentication view's rendering logic may be customized using the appropriate methods available via the `Laravel\Fortify\Fortify` class. Typically, you should call this method from the `boot` method of your `FortifyServiceProvider`:

```php
use Laravel\Fortify\Fortify;

Fortify::registerView(function () {
    return view('auth.register');
});
```

Fortify will take care of generating the `/register` route that returns this view. Your `register` template should include a form that makes a POST request to `/register`. The `/register` action expects a string `name`, string email address / username, `password`, and `password_confirmation` fields. The name of the email / username field should match the `username` value of the `fortify` configuration file.

If the registration attempt is successful, Fortify will redirect you to the URI configured via the `home` configuration option within your `fortify` configuration file. If the login request was an XHR request, a `200` HTTP response will be returned.

If the request was not successful, the user will be redirected back to the registration screen and the validation errors will be available to you via the shared `$errors` Blade template variable. Or, in the case of an XHR request, the validation errors will be returned with the `422` HTTP response.

#### Customizing Registration

The user validation and creation process may be customized by modifying the `App\Actions\Fortify\CreateNewUser` action.

### Password Reset

#### Requesting A Password Reset Link

To begin implementing password reset functionality, we need to instruct Fortify how to return our "forgot password" view. Remember, Fortify is a headless authentication library. If you would like a frontend implementation of Fortify that is already completed for you, you should use [Laravel Jetstream](https://jetstream.laravel.com).

All of the authentication view's rendering logic may be customized using the appropriate methods available via the `Laravel\Fortify\Fortify` class. Typically, you should call this method from the `boot` method of your `FortifyServiceProvider`:

```php
use Laravel\Fortify\Fortify;

Fortify::requestPasswordResetLinkView(function () {
    return view('auth.forgot-password');
});
```

Fortify will take care of generating the `/forgot-password` route that returns this view. Your `forgot-password` template should include a form that makes a POST request to `/forgot-password`. The `/forgot-password` endpoint expects a string `email` field. The name of this field / database column should match the `email` value of the `fortify` configuration file.

If the password reset link request was successful, Fortify will redirect back to the `/forgot-password` route and send an email to the user with a secure link they can use to reset their password. If the request was an XHR request, a `200` HTTP response will be returned.

After being redirected back to the `/forgot-password` route after a successful request, the `status` session variable may be used to display the status of the password reset link request attempt:

```html
@if (session('status'))
    <div class="mb-4 font-medium text-sm text-green-600">
        {{ session('status') }}
    </div>
@endif
```

If the request was not successful, the user will be redirected back to the request password reset link screen and the validation errors will be available to you via the shared `$errors` Blade template variable. Or, in the case of an XHR request, the validation errors will be returned with the `422` HTTP response.

#### Resetting The Password

To finish implementing password reset functionality, we need to instruct Fortify how to return our "reset password" view. Remember, Fortify is a headless authentication library. If you would like a frontend implementation of Fortify that is already completed for you, you should use [Laravel Jetstream](https://jetstream.laravel.com).

All of the authentication view's rendering logic may be customized using the appropriate methods available via the `Laravel\Fortify\Fortify` class. Typically, you should call this method from the `boot` method of your `FortifyServiceProvider`:

```php
use Laravel\Fortify\Fortify;

Fortify::resetPasswordView(function ($request) {
    return view('auth.reset-password', ['request' => $request]);
});
```

Fortify will take care of generating the route to display this view. Your `reset-password` template should include a form that makes a POST request to `/reset-password`. The `/reset-password` endpoint expects a string `email` field, a `password` field, a `password_confirmation` field, and a hidden field named `token` that contains the value of `request()->route('token')`. The name of the "email" field / database column should match the `email` value of the `fortify` configuration file.

If the password reset request was successful, Fortify will redirect back to the `/login` route so that the user can login with their new password. In addition a `status` session variable will be set so that you may display the successful status of the reset on your login screen:

```html
@if (session('status'))
    <div class="mb-4 font-medium text-sm text-green-600">
        {{ session('status') }}
    </div>
@endif
```

If the request was an XHR request, a `200` HTTP response will be returned.

If the request was not successful, the user will be redirected back to the reset password screen and the validation errors will be available to you via the shared `$errors` Blade template variable. Or, in the case of an XHR request, the validation errors will be returned with the `422` HTTP response.

#### Customizing Password Resets

The password reset process may be customized by modifying the `App\Actions\ResetUserPassword` action.

### Email Verification

After registration, you may wish for users to verify their email address before they continue accessing your application. To get started, ensure the `emailVerification` feature is enabled in your `fortify` configuration file's `features` array. Next, you should ensure that your `App\Models\User` class implements the `MustVerifyEmail` interface. This interface is already imported into this model for you.

Once these two setup steps have been completed, newly registered users will receive an email prompting them to verify their email address ownership. However, we need to inform Fortify how to display the email verification screen which informs the user that they need to go click the verification link in the email.

All of the authentication view's rendering logic may be customized using the appropriate methods available via the `Laravel\Fortify\Fortify` class. Typically, you should call this method from the `boot` method of your `FortifyServiceProvider`:

```php
use Laravel\Fortify\Fortify;

Fortify::verifyEmailView(function () {
    return view('auth.verify-email');
});
```

Fortify will take care of generating the route to display this view when a user is redirected to the `/email/verify` endpoint by Laravel's built-in `verified` middleware.

Your `verify-email` template should include an informational message instructing the user to click the email verification link that was sent to their email address. You may optionally add a button to this template that triggers a POST request to `/email/verification-notification`. When this endpoint receives a request, a new verification email link will be emailed to the user, allowing the user to get a new verification link if the previous one was accidentally deleted or lost.

If the request to resend the verification link email was successful, Fortify will redirect back to the `/email/verify` endpoint with a `status` session variable, allowing you to display an informational message to the user informing them the operation was successful. If the request was an XHR request, a `202` HTTP response will be returned.

#### Protecting Routes

To specify that a route or group of routes requires that the user has previously verified their email address, you should attach Laravel's built-in `verified` middleware to the route:

```php
Route::get('/dashboard', function () {
    // ...
})->middleware(['verified']);
```

### Password Confirmation

While building your application, you may occasionally have actions that should require the user to confirm their password before the action is performed. Typically, these routes are protected by Laravel's built-in `password.confirm` middleware. To begin implementing password confirmation functionality, we need to instruct Fortify how to return our "password confirmation" view. Remember, Fortify is a headless authentication library. If you would like a frontend implementation of Fortify that is already completed for you, you should use [Laravel Jetstream](https://jetstream.laravel.com/).

All of the authentication view's rendering logic may be customized using the appropriate methods available via the `Laravel\Fortify\Fortify` class. Typically, you should call this method from the `boot` method of your `FortifyServiceProvider`:

```php
use Laravel\Fortify\Fortify;

Fortify::confirmPasswordView(function () {
    return view('auth.confirm-password');
});
```

Fortify will take care of generating the `/user/confirm-password` route that returns this view. Your `confirm-password` template should include a form that makes a POST request to `/user/confirm-password`. The `/user/confirm-password` action expects a `password` field that contains the user's current password.

If the password matches, Fortify will redirect you to the route the user was attempting to access. If the request was an XHR request, a `201` HTTP response will be returned.

If the request was not successful, the user will be redirected back to the confirm password screen and the validation errors will be available to you via the shared `$errors` Blade template variable. Or, in the case of an XHR request, the validation errors will be returned with the `422` HTTP response.

## Contributing

Thank you for considering contributing to Fortify! You can read the contribution guide [here](.github/CONTRIBUTING.md).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

Please review [our security policy](https://github.com/laravel/fortify/security/policy) on how to report security vulnerabilities.

## License

Laravel Fortify is open-sourced software licensed under the [MIT license](LICENSE.md).
