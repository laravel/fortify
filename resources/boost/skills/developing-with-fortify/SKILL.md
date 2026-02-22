---
name: developing-with-fortify
description: Laravel Fortify headless authentication backend development. Activate when implementing authentication features including login, registration, password reset, email verification, two-factor authentication (2FA/TOTP), profile updates, headless auth, authentication scaffolding, or auth guards in Laravel applications.
---

# Laravel Fortify Development

Fortify is a headless authentication backend that provides authentication routes and controllers for Laravel applications.

## Documentation

Use `search-docs` for detailed Laravel Fortify patterns and documentation.

## Usage

- **Routes**: Use `list-routes` with `only_vendor: true` and `action: "Fortify"` to see all registered endpoints
- **Actions**: Check `app/Actions/Fortify/` for customizable business logic (user creation, password validation, etc.)
- **Config**: See `config/fortify.php` for all options including features, guards, rate limiters, and username field
- **Contracts**: Look in `Laravel\Fortify\Contracts\` for overridable response classes (`LoginResponse`, `LogoutResponse`, etc.)
- **Views**: All view callbacks are set in `FortifyServiceProvider::boot()` using `Fortify::loginView()`, `Fortify::registerView()`, etc.

## Available Features

Enable in `config/fortify.php` features array:

- `Features::registration()` - User registration
- `Features::resetPasswords()` - Password reset via email
- `Features::emailVerification()` - Requires User to implement `MustVerifyEmail`
- `Features::updateProfileInformation()` - Profile updates
- `Features::updatePasswords()` - Password changes
- `Features::twoFactorAuthentication()` - 2FA with QR codes and recovery codes

> Use `search-docs` for feature configuration options and customization patterns.

## Setup Workflows

### Two-Factor Authentication Setup

```
- [ ] Add TwoFactorAuthenticatable trait to User model
- [ ] Enable feature in config/fortify.php
- [ ] If the `*_add_two_factor_columns_to_users_table.php` migration is missing, publish via `php artisan vendor:publish --tag=fortify-migrations` and migrate
- [ ] Set up view callbacks in FortifyServiceProvider
- [ ] Create 2FA management UI
- [ ] Test QR code and recovery codes
```

> Use `search-docs` for TOTP implementation and recovery code handling patterns.

### Email Verification Setup

```
- [ ] Enable emailVerification feature in config
- [ ] Implement MustVerifyEmail interface on User model
- [ ] Set up verifyEmailView callback
- [ ] Add verified middleware to protected routes
- [ ] Test verification email flow
```

> Use `search-docs` for MustVerifyEmail implementation patterns.

### Password Reset Setup

```
- [ ] Enable resetPasswords feature in config
- [ ] Set up requestPasswordResetLinkView callback
- [ ] Set up resetPasswordView callback
- [ ] Define password.reset named route (if views disabled)
- [ ] Test reset email and link flow
```

> Use `search-docs` for custom password reset flow patterns.

### SPA Authentication Setup

```
- [ ] Set 'views' => false in config/fortify.php
- [ ] Install and configure Laravel Sanctum for session-based SPA authentication
- [ ] Use the 'web' guard in config/fortify.php (required for session-based authentication)
- [ ] Set up CSRF token handling
- [ ] Test XHR authentication flows
```

> Use `search-docs` for integration and SPA authentication patterns.

#### Two-Factor Authentication in SPA Mode

When `views` is set to `false`, Fortify returns JSON responses instead of redirects.

If a user attempts to log in and two-factor authentication is enabled, the login request will return a JSON response indicating that a two-factor challenge is required:

```json
{
    "two_factor": true
}
```

## Best Practices

### Custom Authentication Logic

Override authentication behavior using `Fortify::authenticateUsing()` for custom user retrieval or `Fortify::authenticateThrough()` to customize the authentication pipeline. Override response contracts in `AppServiceProvider` for custom redirects.

### Registration Customization

Modify `app/Actions/Fortify/CreateNewUser.php` to customize user creation logic, validation rules, and additional fields.

### Rate Limiting

Configure via `fortify.limiters.login` in config. Default configuration throttles by username + IP combination.

## Key Endpoints

| Feature                | Method   | Endpoint                                    |
|------------------------|----------|---------------------------------------------|
| Login                  | POST     | `/login`                                    |
| Logout                 | POST     | `/logout`                                   |
| Register               | POST     | `/register`                                 |
| Password Reset Request | POST     | `/forgot-password`                          |
| Password Reset         | POST     | `/reset-password`                           |
| Email Verify Notice    | GET      | `/email/verify`                             |
| Resend Verification    | POST     | `/email/verification-notification`          |
| Password Confirm       | POST     | `/user/confirm-password`                    |
| Enable 2FA             | POST     | `/user/two-factor-authentication`           |
| Confirm 2FA            | POST     | `/user/confirmed-two-factor-authentication` |
| 2FA Challenge          | POST     | `/two-factor-challenge`                     |
| Get QR Code            | GET      | `/user/two-factor-qr-code`                  |
| Recovery Codes         | GET/POST | `/user/two-factor-recovery-codes`           |

## Error Handling

Laravel Fortify returns standard Laravel validation and authentication error responses.  
This section covers View mode and SPA (headless) mode.

---

### Validation Errors

When validation fails, Fortify returns a `422 Unprocessable Entity` response.

**View-enabled mode:**
- Users are redirected back
- Validation errors are flashed to the session

**SPA mode (`'views' => false`):**
- A JSON response is returned
- Errors are included in the `errors` object

Example JSON response:

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": [
            "The email field is required."
        ],
        "password": [
            "The password must be at least 8 characters."
        ]
    }
}
```

---

### Authentication Failures

If authentication fails (invalid credentials):

- **View mode:** Redirect back with errors
- **SPA mode:** JSON response with validation error message (typically 422 status code)

Example JSON response:

```json
{
    "message": "These credentials do not match our records."
}
```

---

### Two-Factor Challenge Response (SPA Mode)

If two-factor authentication is enabled and a challenge is required, the login attempt will return:

```json
{
    "two_factor": true
}
```

The client should then redirect the user to the `/two-factor-challenge` endpoint.

---

### Rate Limiting Errors

If too many login attempts are made, Fortify returns a validation error.

**SPA mode:** typically returns a `429 Too Many Requests` response

Example JSON response:

```json
{
    "message": "Too many login attempts. Please try again in 60 seconds."
}
```
