<?php

namespace Laravel\Fortify;

use Illuminate\Contracts\Cache\Repository;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider as TwoFactorAuthenticationProviderContract;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthenticationProvider implements TwoFactorAuthenticationProviderContract
{
    /**
     * The underlying library providing two factor authentication helper services.
     *
     * @var \PragmaRX\Google2FA\Google2FA
     */
    protected $engine;

    /**
     * The cache repository implementation.
     *
     * @var \Illuminate\Contracts\Cache\Repository|null
     */
    protected $cache;

    /**
     * Create a new two factor authentication provider instance.
     *
     * @param  \PragmaRX\Google2FA\Google2FA  $engine
     * @param  \Illuminate\Contracts\Cache\Repository|null  $cache
     * @return void
     */
    public function __construct(Google2FA $engine, Repository $cache = null)
    {
        $this->engine = $engine;
        $this->cache = $cache;
    }

    /**
     * Generate a new secret key.
     *
     * @return string
     */
    public function generateSecretKey()
    {
        return $this->engine->generateSecretKey();
    }

    /**
     * Get the two factor authentication QR code URL.
     *
     * @param  string  $companyName
     * @param  string  $companyEmail
     * @param  string  $secret
     * @return string
     */
    public function qrCodeUrl($companyName, $companyEmail, $secret)
    {
        return $this->engine->getQRCodeUrl($companyName, $companyEmail, $secret);
    }

    /**
     * Verify the given code.
     *
     * @param  string  $secret
     * @param  string  $code
     * @return bool
     */
    public function verify($secret, $code)
    {
        if (is_int($customWindow = config('fortify-options.two-factor-authentication.window'))) {
            $this->engine->setWindow($customWindow);
        }

        $timestamp = $this->engine->verifyKeyNewer(
            $secret, $code, optional($this->cache)->get($key = 'fortify.2fa_codes.'.md5($code))
        );

        if ($timestamp !== false) {
            optional($this->cache)->put($key, $timestamp, ($this->engine->getWindow() ?: 1) * 60);

            return true;
        }

        return false;
    }
}
