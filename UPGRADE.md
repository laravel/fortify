# Upgrade Guide

Future upgrade notes will be placed here.

---

## Upgrading to 2.x from 1.x

### Confirm Password Feature Disabled by Default

> **If you're using two-factor authentication with `confirmPassword`, no changes are needed; otherwise, enable password confirmation manually.**

If your application is already using two-factor authentication with `confirmPassword` enabled, no changes are required — everything should continue to work as before:

```php
'features' => [
    // ...
    Features::twoFactorAuthentication([
        'confirmPassword' => true,
    ]),
    // ...
],
```

If your application is **not** using two-factor authentication but **is** using `password.confirm` routes, you will need to enable the password confirmation feature in your `fortify.php` configuration file by adding the following line to the `features` array:

```php
'features' => [
    // ...
    Features::passwordConfirmation(),
    // ...
],
```

## Upgrading To 1.7.3 From 1.x

### Two Factor Brute Force Attack Security Fix

Fortify 1.7.3 includes a security fix to prevent potential brute force attacks against the two factor authentication code form when a malicious user already knows another user's email address and password. To fully enable the security fix, you will need to enable two factor rate limiting in your application's `fortify.php` configuration file:

```php
 'limiters' => [
     'login' => 'login',
     'two-factor' => 'two-factor',
 ],
 ```

Next, define the `two-factor` rate limiter in the `boot` method of your application's `FortifyServiceProvider`:

```php
RateLimiter::for('two-factor', function (Request $request) {
    return Limit::perMinute(5)->by($request->session()->get('login.id'));
});
```
