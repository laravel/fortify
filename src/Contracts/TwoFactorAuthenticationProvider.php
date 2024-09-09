<?php

namespace Laravel\Fortify\Contracts;

interface TwoFactorAuthenticationProvider
{
    /**
     * Generate a new secret key.
     *
     * @param  int  $secret_length
     * @return string
     */
    public function generateSecretKey($secret_length);

    /**
     * Get the two factor authentication QR code URL.
     *
     * @param  string  $companyName
     * @param  string  $companyEmail
     * @param  string  $secret
     * @return string
     */
    public function qrCodeUrl($companyName, $companyEmail, $secret);

    /**
     * Verify the given token.
     *
     * @param  string  $secret
     * @param  string  $code
     * @return bool
     */
    public function verify($secret, $code);
}
