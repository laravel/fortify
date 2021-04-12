<p align="center"><img src="/art/logo.svg" alt="Logo Laravel Fortify"></p>

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

## Official Documentation

Documentation for Fortify can be found on the [Laravel website](https://laravel.com/docs/fortify).

## Contributing

Thank you for considering contributing to Fortify! You can read the contribution guide [here](.github/CONTRIBUTING.md).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

Please review [our security policy](https://github.com/laravel/fortify/security/policy) on how to report security vulnerabilities.

## License

Laravel Fortify is open-sourced software licensed under the [MIT license](LICENSE.md).

## Comments 

There are a small documentation inconsistencies for declaring views. Some code examples are in php 7.4 syntax instead of 7.3. 
And, having in mind, that Laravel 8 minimum requirement is php 7.3, then syntax from php 7.4 (new features) are not correct in this context.

Code examples that are NOT OK (7.4 syntax used - closure), are for:
- Login (https://laravel.com/docs/8.x/fortify#authentication)
- Registration (https://laravel.com/docs/8.x/fortify#registration)
- Email Verification (https://laravel.com/docs/8.x/fortify#email-verification)

Code examples that are OK (7.3 syntax), are for:
- Requesting A Password Reset Link (https://laravel.com/docs/8.x/fortify#requesting-a-password-reset-link)
- Resetting The Password (https://laravel.com/docs/8.x/fortify#resetting-the-password) 
- ... 

I did not check the whole documentation, so please do that in the context of php syntax (to be valid with php 7.3). 
Thank you, and best wishes. 
