<?php

namespace Laravel\Fortify\Actions;

use Illuminate\Support\Collection;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\RecoveryCode;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;
use PragmaRX\Google2FA\Support\Constants;

class EnableTwoFactorAuthentication
{
    /**
     * The two factor authentication provider.
     *
     * @var \Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider
     */
    protected $provider;

    /**
     * Create a new action instance.
     *
     * @param  \Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider  $provider
     * @return void
     */
    public function __construct(TwoFactorAuthenticationProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Enable two factor authentication for the user after checking if a optional given secret is valid.
     *
     * @param  mixed  $user
     * @param ?string $secret
     * @throws \PragmaRX\Google2FA\Exceptions\InvalidCharactersException
     * @throws \PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException
     * @throws \PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException
     * @return void
     */
    public function __invoke($user, $secret = null)
    {
        if ($secret) {
            $this->validateSecret($secret);
        }

        $user->forceFill([
            'two_factor_secret' => (! $secret ? encrypt($this->provider->generateSecretKey()) : encrypt($secret)),
            'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, function () {
                return RecoveryCode::generate();
            })->all())),
        ])->save();
    }

    /**
     * Validate the secret.
     *
     * @param string $b32
     *
     * @throws InvalidCharactersException
     * @throws SecretKeyTooShortException
     * @throws IncompatibleWithGoogleAuthenticatorException
     */
    protected function validateSecret($b32)
    {
        $this->checkForValidCharacters($b32);

        $this->checkGoogleAuthenticatorCompatibility($b32);

        $this->checkIsBigEnough($b32);
    }

    /**
     * Calculate char count bits.
     *
     * @param string $b32
     *
     * @return int
     */
    protected function charCountBits($b32)
    {
        return strlen($b32) * 8;
    }

    /**
     * Check if the string length is power of two.
     *
     * @param string $b32
     *
     * @return bool
     */
    protected function isCharCountNotAPowerOfTwo($b32)
    {
        return (strlen($b32) & (strlen($b32) - 1)) !== 0;
    }

    /**
     * Check if the secret key is compatible with Google Authenticator.
     *
     * @param string $b32
     *
     * @throws IncompatibleWithGoogleAuthenticatorException
     */
    protected function checkGoogleAuthenticatorCompatibility($b32)
    {
        if (
            $this->isCharCountNotAPowerOfTwo($b32) // Google Authenticator requires it to be a power of 2 base32 length string
        ) {
            throw new IncompatibleWithGoogleAuthenticatorException();
        }
    }

    /**
     * Check if all secret key characters are valid.
     *
     * @param string $b32
     *
     * @throws InvalidCharactersException
     */
    protected function checkForValidCharacters($b32)
    {
        if (
            preg_replace('/[^'.Constants::VALID_FOR_B32.']/', '', $b32) !==
            $b32
        ) {
            throw new InvalidCharactersException();
        }
    }

    /**
     * Check if secret key length is big enough.
     *
     * @param string $b32
     *
     * @throws SecretKeyTooShortException
     */
    protected function checkIsBigEnough($b32)
    {
        // Minimum = 128 bits
        // Recommended = 160 bits
        // Compatible with Google Authenticator = 256 bits

        if (
            $this->charCountBits($b32) < 128
        ) {
            throw new SecretKeyTooShortException();
        }
    }

}
