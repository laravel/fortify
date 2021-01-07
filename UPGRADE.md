# Upgrade Guide

Future upgrade notes will be placed here.


## Upgrading To 1.7.3 from 1.x

### Brute Force Attack Security Fix

Fortify v1.7.3 was released with a security fix that fixed a vulnerability with 2FA and a potential Brute Force Attack. To fully enable the security fix you'll need to enable rate limiting in your `fortify.php` config file. Change the following lines below:

```php
     'limiters' => [
         'login' => null,
     ],
```

To these:

```php
     'limiters' => [
         'login' => 'login',
         'two-factor' => 'two-factor',
     ],
 ```

 This will enable rate limiting on the login and Two Factor screens.
