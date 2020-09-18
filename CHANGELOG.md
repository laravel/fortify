# Release Notes

## [Unreleased](https://github.com/laravel/fortify/compare/v1.4.0...1.x)


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
