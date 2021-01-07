# Upgrade Guide

Future upgrade notes will be placed here.

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
