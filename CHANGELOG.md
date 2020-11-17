# Release Notes

## [Unreleased](https://github.com/laravel/fortify/compare/v1.7.1...1.x)


## [v1.7.1 (2020-11-13)](https://github.com/laravel/fortify/compare/v1.7.0...v1.7.1)

### Changed
- Add the `prefix` and `domain` configuration options ([#143](https://github.com/laravel/fortify/pull/143))
- Change how feature options are stored to work with config caching ([b2430958](https://github.com/laravel/fortify/commit/b2430958fa93883ab0e5f0caf486ef3688711608))

### Fixed
- Fix 2FA disabled routes via `views` config ([#142](https://github.com/laravel/fortify/pull/142))


## [v1.7.0 (2020-11-03)](https://github.com/laravel/fortify/compare/v1.6.2...v1.7.0)

### Added
- PHP 8 Support ([#130](https://github.com/laravel/fortify/pull/130))
- Add `views` config option ([#133](https://github.com/laravel/fortify/pull/133), [ff155d0](https://github.com/laravel/fortify/commit/ff155d0e136d67bfd3832e052ed54135525c4569))


## [v1.6.2 (2020-10-20)](https://github.com/laravel/fortify/compare/v1.6.1...v1.6.2)

### Changed
- Redirect to intended URL after email verification ([#119](https://github.com/laravel/fortify/pull/119))
- Only use two factor action when enabled ([#127](https://github.com/laravel/fortify/pull/127))


## [v1.6.1 (2020-10-05)](https://github.com/laravel/fortify/compare/v1.6.0...v1.6.1)

### Added
- Add FailedTwoFactorLoginResponse contract ([#106](https://github.com/laravel/fortify/pull/106))

### Changed
- Redirect to intended after two factor login ([#105](https://github.com/laravel/fortify/pull/105))
- Allow Fortify views to accept `Responsable` objects ([#107](https://github.com/laravel/fortify/pull/107))
- Use the `Rule::unique` for new user validation ([#108](https://github.com/laravel/fortify/pull/108))


## [v1.6.0 (2020-09-29)](https://github.com/laravel/fortify/compare/v1.5.0...v1.6.0)

### Added
- Add `attempts` method to rate limiter ([#85](https://github.com/laravel/fortify/pull/85))
- Add name to Profile update and Password update routes ([#89](https://github.com/laravel/fortify/pull/89))

### Fixed
- Fix for empty password during confirmation ([#87](https://github.com/laravel/fortify/pull/87))


## [v1.5.0 (2020-09-22)](https://github.com/laravel/fortify/compare/v1.4.3...v1.5.0)

### Added
- Add option to force the password to have a special character ([#65](https://github.com/laravel/fortify/pull/65))

### Fixed
- Allow 'confirmPasswordView' to use view prefixes ([#71](https://github.com/laravel/fortify/pull/71))
- Send JSON response if request is an AJAX request ([#75](https://github.com/laravel/fortify/pull/75))


## [v1.4.3 (2020-09-20)](https://github.com/laravel/fortify/compare/v1.4.2...v1.4.3)

### Fixed
- Fix flawed logic in the `UpdateUserProfileInformation` action ([#68](https://github.com/laravel/fortify/pull/68), [fea6473](https://github.com/laravel/fortify/commit/fea64739156be75d9d382ca680afaf33c16cce3f), [91518af](https://github.com/laravel/fortify/commit/91518afbf3ce33d3e6b2a36b032e67e8474367e9))


## [v1.4.2 (2020-09-20)](https://github.com/laravel/fortify/compare/v1.4.1...v1.4.2)

### Changed
- Remove unnecessary bag ([85a7dfb](https://github.com/laravel/fortify/commit/85a7dfbc75229782a2cb985366a6565792c541be))

### Fixed
- Fix test bug when use sqlite database ([#69](https://github.com/laravel/fortify/pull/69))


## [v1.4.1 (2020-09-18)](https://github.com/laravel/fortify/compare/v1.4.0...v1.4.1)

### Added
- Allow the expected email address request variable to be changed ([#28](https://github.com/laravel/fortify/pull/28))
- Update configuration stub with middleware option ([#55](https://github.com/laravel/fortify/pull/55))

### Changed
- Make routes more dynamic ([#41](https://github.com/laravel/fortify/pull/41))
- Add illuminate/support dependency ([#46](https://github.com/laravel/fortify/pull/46))
- Resend email verification after user update ([#52](https://github.com/laravel/fortify/pull/52), [951d943](https://github.com/laravel/fortify/commit/951d943defb44cb44fd92b719ca2db2dba1f297c))

### Fixed
- Only register two-factor-challenge routes if TFA feature enabled ([#44](https://github.com/laravel/fortify/pull/44))
- Added missing request to the throwFailedAuthenticationException method ([#61](https://github.com/laravel/fortify/pull/61))


## [v1.4.0 (2020-09-14)](https://github.com/laravel/fortify/compare/v1.3.1...v1.4.0)

### Added
- Confirmed password status controller ([fe5821f](https://github.com/laravel/fortify/commit/fe5821fe7562503330ae63962cd0edf379ca52e3))
- Configurable password timeout ([f0a6477](https://github.com/laravel/fortify/commit/f0a6477212fd99197fa8ef77eff90cf7249a3271), [2b4fa36](https://github.com/laravel/fortify/commit/2b4fa366377a24705b0ee3525d7c2d456f1fec4b))

### Changed
- Switch the TwoFactorLoginResponse for a contract bound in container ([#34](https://github.com/laravel/fortify/pull/34))
- Enable password confirmation ([9e9d154](https://github.com/laravel/fortify/commit/9e9d15465285b26a45919392bf28fef877e5bb5f))


## [v1.3.1 (2020-09-11)](https://github.com/laravel/fortify/compare/v1.3.0...v1.3.1)

### Changed
- Extract `ConfirmPassword` action ([a9e68f2](https://github.com/laravel/fortify/commit/a9e68f2c47c598937b1705161ba2dff216315085))

### Fixed
- Update what is passed to custom callback ([9215e54](https://github.com/laravel/fortify/commit/9215e54263b913d71e309e7cfbe880a94112fd96))


## [v1.3.0 (2020-09-11)](https://github.com/laravel/fortify/compare/v1.2.1...v1.3.0)

### Added
- Google2fa v8 support ([#25](https://github.com/laravel/fortify/pull/25))
- Add support for password confirmation ([#6](https://github.com/laravel/fortify/pull/6), [3ed5e87](https://github.com/laravel/fortify/commit/3ed5e87e40adf69ffa770fca9f317707e79eff95), [865ed4f](https://github.com/laravel/fortify/commit/865ed4f6ea055ef00d1b74e32a9e3b0dfe5af19e), [c58a2fb](https://github.com/laravel/fortify/commit/c58a2fbd1b3bb95a7f06beb8b17dcd8c9f1553e6))


## [v1.2.1 (2020-09-10)](https://github.com/laravel/fortify/compare/v1.2.0...v1.2.1)

### Fixed
- Pass request through to the callback ([#21](https://github.com/laravel/fortify/pull/21))


## [v1.2.0 (2020-09-10)](https://github.com/laravel/fortify/compare/v1.1.0...v1.2.0)

### Added
- Allow granular authentication customization ([cd8b6aa](https://github.com/laravel/fortify/commit/cd8b6aa15e2842d691d683610bc5c04f61f1abb6))


## [v1.1.0 (2020-09-10)](https://github.com/laravel/fortify/compare/v1.0.1...v1.1.0)

### Added
- Allow full customization of authentication pipeline ([6c36b08](https://github.com/laravel/fortify/commit/6c36b080c086196b5ca36d12c2e52658e95141dc))


## [v1.0.1 (2020-09-08)](https://github.com/laravel/fortify/compare/v1.0.0...v1.0.1)

### Changed
- Use PasswordValidationRules trait in CreateNewUser action ([#18](https://github.com/laravel/fortify/pull/18))
- Callable customization of any view ([661d726](https://github.com/laravel/fortify/commit/661d726f9e4462ace0210a31a83e35f39e2efaf0))


## [v1.0.0 (2020-09-08)](https://github.com/laravel/fortify/compare/v0.0.1...v1.0.0)

Initial stable release.
