# Release Notes

## [Unreleased](https://github.com/laravel/fortify/compare/v1.19.0...1.x)

## [v1.19.0](https://github.com/laravel/fortify/compare/v1.18.1...v1.19.0) - 2023-11-27

- Add new event by @taylorotwell in https://github.com/laravel/fortify/commit/2da721fead1f3bc18af983e4903c4e1df67177e7

## [v1.18.1](https://github.com/laravel/fortify/compare/v1.18.0...v1.18.1) - 2023-10-18

- Fix paths in default config using nested arrays by [@sebj54](https://github.com/sebj54) in https://github.com/laravel/fortify/pull/501

## [v1.18.0](https://github.com/laravel/fortify/compare/v1.17.6...v1.18.0) - 2023-09-12

- Added case-sensitivity option for usernames by [@Radiergummi](https://github.com/Radiergummi) in https://github.com/laravel/fortify/pull/485
- Added response contract for email verification notification by [@m-thalmann](https://github.com/m-thalmann) in https://github.com/laravel/fortify/pull/489

## [v1.17.6](https://github.com/laravel/fortify/compare/v1.17.5...v1.17.6) - 2023-09-04

- Update logout to invalidate and regenerate session only if session is present (Issue #486) by [@karmendra](https://github.com/karmendra) in https://github.com/laravel/fortify/pull/487

## [v1.17.5](https://github.com/laravel/fortify/compare/v1.17.4...v1.17.5) - 2023-08-02

- [1.x] Laravel Pint fixes by [@iruoy](https://github.com/iruoy) in https://github.com/laravel/fortify/pull/480

## [v1.17.4](https://github.com/laravel/fortify/compare/v1.17.3...v1.17.4) - 2023-06-18

- Port security fixes to default login rate limiter by @staudenmeir in https://github.com/laravel/fortify/pull/473

## [v1.17.3](https://github.com/laravel/fortify/compare/v1.17.2...v1.17.3) - 2023-06-02

- Fix contract implementation by @jessarcher in https://github.com/laravel/fortify/pull/472

## [v1.17.2](https://github.com/laravel/fortify/compare/v1.17.1...v1.17.2) - 2023-04-26

- Revert "Add rate limiter for a registration" by @taylorotwell in https://github.com/laravel/fortify/pull/465

## [v1.17.1](https://github.com/laravel/fortify/compare/v1.17.0...v1.17.1) - 2023-04-19

- Add rate limiter for a registration by @trbsi in https://github.com/laravel/fortify/pull/460

## [v1.17.0](https://github.com/laravel/fortify/compare/v1.16.0...v1.17.0) - 2023-04-17

- Add ability to override routes with custom paths by @stephenglass in https://github.com/laravel/fortify/pull/458

## [v1.16.0](https://github.com/laravel/fortify/compare/v1.15.0...v1.16.0) - 2023-01-06

### Added

- Laravel v10 Support by @driesvints in https://github.com/laravel/fortify/pull/435

## [v1.15.0](https://github.com/laravel/fortify/compare/v1.14.1...v1.15.0) - 2023-01-03

### Changed

- Update PrepareAuthenticatedSession.php by @francoism90 in https://github.com/laravel/fortify/pull/434
- Uses PHP Native Type Declarations 🐘 by @nunomaduro in https://github.com/laravel/fortify/pull/421

### Fixed

- Fix error while preparing PasswordResetResponse with views turned off by @leonkllr0 in https://github.com/laravel/fortify/pull/433

## [v1.14.1](https://github.com/laravel/fortify/compare/v1.14.0...v1.14.1) - 2022-12-09

### Changed

- Only fire event when actually updating the database to disable two factor authentication by @taylorotwell in https://github.com/laravel/fortify/commit/04b4b9c20e421c415d0427904a72e08a21bdec27

## [v1.14.0](https://github.com/laravel/fortify/compare/v1.13.7...v1.14.0) - 2022-11-23

### Added

- Add more Response contract bindings by @bdsumon4u in https://github.com/laravel/fortify/pull/425

## [v1.13.7](https://github.com/laravel/fortify/compare/v1.13.6...v1.13.7) - 2022-11-04

### Changed

- Update parameter order for hash_equals function in TwoFactorLoginRequest by @jayan-blutui in https://github.com/laravel/fortify/pull/422

### Fixed

- Use `boolean` rather than `filled` for remember by @Codeatron5000 in https://github.com/laravel/fortify/pull/423

## [v1.13.6](https://github.com/laravel/fortify/compare/v1.13.5...v1.13.6) - 2022-11-01

### Fixed

- Fix error message when entering invalid 2fa code by @emargareten in https://github.com/laravel/fortify/pull/415
- Use Fortify username method on ConfirmPassword action by @jayan-blutui in https://github.com/laravel/fortify/pull/420

## [v1.13.5](https://github.com/laravel/fortify/compare/v1.13.4...v1.13.5) - 2022-10-21

### Changed

- Add and use constants for session flashes by @dwightwatson in https://github.com/laravel/fortify/pull/409
- Use current_password rule when changing password by @dwightwatson in https://github.com/laravel/fortify/pull/410
- Parameters order with hash_equals by @chivincent in https://github.com/laravel/fortify/pull/411

## [v1.13.4](https://github.com/laravel/fortify/compare/v1.13.3...v1.13.4) - 2022-09-30

### Fixed

- Only save user if need to by @taylorotwell in https://github.com/laravel/fortify/commit/9a68cf2deb37d1796b6e2fd97d3c61f086868914

## [v1.13.3](https://github.com/laravel/fortify/compare/v1.13.2...v1.13.3) - 2022-08-16

### Changed

- Return recovery errors under the `recovery_code` key by @jessarcher in https://github.com/laravel/fortify/pull/401

## [v1.13.2](https://github.com/laravel/fortify/compare/v1.13.1...v1.13.2) - 2022-08-09

### Fixed

- Fix second usage of 2FA code by @xwillq in https://github.com/laravel/fortify/pull/399

## [v1.13.1](https://github.com/laravel/fortify/compare/v1.13.0...v1.13.1) - 2022-07-05

### Fixed

- Call FailedTwoFactorLoginResponse::toResponse with TwoFactorLoginRequest by @ricklambrechts in https://github.com/laravel/fortify/pull/395

## [v1.13.0](https://github.com/laravel/fortify/compare/v1.12.0...v1.13.0) - 2022-05-05

### Added

- Added config option for custom OTP window by @robtesch in https://github.com/laravel/fortify/pull/385

## [v1.12.0](https://github.com/laravel/fortify/compare/v1.11.2...v1.12.0) - 2022-03-29

### Changed

- 2FA setup key by @ps-sean in https://github.com/laravel/fortify/pull/371
- Enable 2FA confirmation by default by @taylorotwell in https://github.com/laravel/fortify/commit/a6caadc80e348755de0e1da221a6253d9f2c48f9

### Fixed

- Fix double error message for failed 2FA response by @driesvints in https://github.com/laravel/fortify/pull/369

## [v1.11.2](https://github.com/laravel/fortify/compare/v1.11.1...v1.11.2) - 2022-03-08

### Fixed

- Ensures route `password.confirm` is defined when not using views by @Frozire in https://github.com/laravel/fortify/pull/368

### Security

- Cache 2FA token timestamp by @driesvints in https://github.com/laravel/fortify/pull/366

## [v1.11.1](https://github.com/laravel/fortify/compare/v1.11.0...v1.11.1) - 2022-02-24

### Fixed

- Fix Exception when sending empty 2FA confirmation code by @srdante in https://github.com/laravel/fortify/pull/361
- Unsupported operand types on rollback migration by @Jackpump in https://github.com/laravel/fortify/pull/362

## [v1.11.0](https://github.com/laravel/fortify/compare/v1.10.2...v1.11.0) - 2022-02-22

### Changed

- Include the otpauth url when retrieving the QR svg by @JanMisker in https://github.com/laravel/fortify/pull/356
- Confirmable 2FA by @taylorotwell in https://github.com/laravel/fortify/pull/358

## Fixed

- Fix incorrect key for error bag by @vaibhavpandeyvpz in https://github.com/laravel/fortify/pull/360

## [v1.10.2](https://github.com/laravel/fortify/compare/v1.10.1...v1.10.2) - 2022-02-08

### Changed

- Prevent new login after 2FA challenge ([#353](https://github.com/laravel/fortify/pull/353))

### Security

- Fix throttle bypass exploit ([#354](https://github.com/laravel/fortify/pull/354))

## [v1.10.1](https://github.com/laravel/fortify/compare/v1.10.0...v1.10.1) - 2022-02-01

### Changed

- Fix VerifyEmailResponse resolving ([#349](https://github.com/laravel/fortify/pull/349))

## [v1.10.0 (2022-01-25)](https://github.com/laravel/fortify/compare/v1.9.0...v1.10.0)

### Added

- Add VerifyEmailResponse contract ([#347](https://github.com/laravel/fortify/pull/347))

### Changed

- Switch to anonymous migrations ([#348](https://github.com/laravel/fortify/pull/348))

## [v1.9.0 (2022-01-12)](https://github.com/laravel/fortify/compare/v1.8.6...v1.9.0)

### Changed

- Add 2fa Events ([#338](https://github.com/laravel/fortify/pull/338))
- Laravel 9 support ([#340](https://github.com/laravel/fortify/pull/340))

## [v1.8.6 (2021-12-22)](https://github.com/laravel/fortify/compare/v1.8.5...v1.8.6)

### Changed

- Customise the auth middleware name ([#335](https://github.com/laravel/fortify/pull/335))

### Fixed

- Check if authenticated user has 2FA enabled ([#334](https://github.com/laravel/fortify/pull/334))

## [v1.8.5 (2021-12-07)](https://github.com/laravel/fortify/compare/v1.8.4...v1.8.5)

### Fixed

- Fix an issue with array to string conversion ([#333](https://github.com/laravel/fortify/pull/333))

## [v1.8.4 (2021-11-23)](https://github.com/laravel/fortify/compare/v1.8.3...v1.8.4)

### Changed

- Use boolean rather than filled for remember ([#328](https://github.com/laravel/fortify/pull/328))

## [v1.8.3 (2021-11-02)](https://github.com/laravel/fortify/compare/v1.8.2...v1.8.3)

### Changed

- Add a check for two factor auth being enabled ([#323](https://github.com/laravel/fortify/pull/323))

## [v1.8.2 (2021-09-07)](https://github.com/laravel/fortify/compare/v1.8.1...v1.8.2)

### Changed

- Allow verification rate limiter to be configurable ([#313](https://github.com/laravel/fortify/pull/313))

## [v1.8.1 (2021-08-24)](https://github.com/laravel/fortify/compare/v1.8.0...v1.8.1)

### Changed

- Allow reset password redirect ([#307](https://github.com/laravel/fortify/pull/307))

## [v1.8.0 (2021-08-10)](https://github.com/laravel/fortify/compare/v1.7.14...v1.8.0)

### Added

- Redirection customization ([#298](https://github.com/laravel/fortify/pull/298))
- Add ReplacedRecoveryCode event ([#301](https://github.com/laravel/fortify/pull/301))

### Fixed

- Fix auth guard ([#296](https://github.com/laravel/fortify/pull/296))

## [v1.7.14 (2021-06-29)](https://github.com/laravel/fortify/compare/v1.7.13...v1.7.14)

### Changed

- Add password update custom response ([#290](https://github.com/laravel/fortify/pull/290), [e2a16f6](https://github.com/laravel/fortify/commit/e2a16f6721c6137339ec8b98cc4995865e70ab7f))

## [v1.7.13 (2021-05-25)](https://github.com/laravel/fortify/compare/v1.7.12...v1.7.13)

### Changed

- Cleanup code ([#261](https://github.com/laravel/fortify/pull/261))
- Returns JSON response ([#267](https://github.com/laravel/fortify/pull/267))
- Naming 2FA routes ([#269](https://github.com/laravel/fortify/pull/269))

## [v1.7.12 (2021-04-27)](https://github.com/laravel/fortify/compare/v1.7.11...v1.7.12)

### Changed

- Restrict guest Middleware to Fortify's guard ([#258](https://github.com/laravel/fortify/pull/258))

## [v1.7.11 (2021-04-20)](https://github.com/laravel/fortify/compare/v1.7.10...v1.7.11)

### Fixed

- Remove password confirmation requirement for reset password ([#254](https://github.com/laravel/fortify/pull/254))

## [v1.7.10 (2021-04-13)](https://github.com/laravel/fortify/compare/v1.7.9...v1.7.10)

### Fixed

- Better way of validating credentials ([#248](https://github.com/laravel/fortify/pull/248))
- Use configured username property for qr code url ([#249](https://github.com/laravel/fortify/pull/249))

## [v1.7.9 (2021-03-30)](https://github.com/laravel/fortify/compare/v1.7.8...v1.7.9)

### Fixed

- Require password and confirmation ([#245](https://github.com/laravel/fortify/pull/245))

## [v1.7.8 (2021-03-09)](https://github.com/laravel/fortify/compare/v1.7.7...v1.7.8)

### Fixed

- Fix two factor form without user ([#233](https://github.com/laravel/fortify/pull/233), [67d7743](https://github.com/laravel/fortify/commit/67d7743c843fa01e2a9c5f089ad4aee3dc561743), [#235](https://github.com/laravel/fortify/pull/235))

## [v1.7.7 (2021-02-23)](https://github.com/laravel/fortify/compare/v1.7.6...v1.7.7)

### Fixed

- Redirect to intended URL after registration ([#222](https://github.com/laravel/fortify/pull/222))

## [v1.7.6 (2021-02-16)](https://github.com/laravel/fortify/compare/v1.7.5...v1.7.6)

### Fixed

- Fix password rule ([#211](https://github.com/laravel/fortify/pull/211))
- Adds a missing scenario for the password rule ([#213](https://github.com/laravel/fortify/pull/213))

## [v1.7.5 (2021-01-19)](https://github.com/laravel/fortify/compare/v1.7.4...v1.7.5)

### Fixed

- Move route outside `$enableViews` ([#203](https://github.com/laravel/fortify/pull/203))

## [v1.7.4 (2021-01-07)](https://github.com/laravel/fortify/compare/v1.7.3...v1.7.4)

### Fixed

- Fix missing current password ([#194](https://github.com/laravel/fortify/pull/194))

### Security

- Revert "Retrieve user through provider" ([#195](https://github.com/laravel/fortify/pull/195))

## [v1.7.3 (2021-01-05)](https://github.com/laravel/fortify/compare/v1.7.2...v1.7.3)

### Changed

- Retrieve user through provider ([#189](https://github.com/laravel/fortify/pull/189))

### Fixed

- Tweak how rate limiting is implemented ([8609af2](https://github.com/laravel/fortify/commit/8609af2292652234a70e4457d63ff1e10a510631))
- Fix Two Factor prepare auth session ([#181](https://github.com/laravel/fortify/pull/181))

## [v1.7.2 (2020-11-24)](https://github.com/laravel/fortify/compare/v1.7.1...v1.7.2)

### Fixed

- Fix route prefix ([#152](https://github.com/laravel/fortify/pull/152))
- Fire Failed events ([#154](https://github.com/laravel/fortify/pull/154))

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
