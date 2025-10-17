## Laravel Fortify

Fortify is a headless authentication backend that provides routes and controllers but no views.
**Before implementing any auth features, use the `search-docs` tool to get the latest docs for that specific feature.**

### Configuration & Setup
- Check `config/fortify.php` to see what's enabled. Use `search-docs` for detailed info on specific features.
- Enable features by adding them to the `'features' => []` array: `Features::registration()`, `Features::resetPasswords()`, etc.
- To see the all fortify registered routes use the `list-routes` tool with `only_vendor: true` and `action: "Fortify"` parameters.
- Fortify includes view routes by default (login, register screens). Set `'views' => false` in config to disable them if you're handling views yourself.

### Customization
- Customize views in `FortifyServiceProvider`'s `boot()` method using `Fortify::loginView()`, `Fortify::registerView()`, etc.
- Customize authentication logic with `Fortify::authenticateUsing()` for custom user retrieval/validation.
- Actions in `app/Actions/Fortify/` handle business logic (user creation, password reset, etc.). They're fully customizable, so modify them to change feature behavior.

## Available Features
- `Features::registration()` for user registration
- `Features::resetPasswords()` for password reset via email
- `Features::emailVerification()` to verify new user emails
- `Features::updateProfileInformation()` to let users update their profile
- `Features::updatePasswords()` to let users change passwords
- `Features::twoFactorAuthentication()` for 2FA with QR codes and recovery codes
  - Add options: `['confirmPassword'=>true, 'confirm' => true]` to require password confirmation and confirm otp before enabling 2FA
