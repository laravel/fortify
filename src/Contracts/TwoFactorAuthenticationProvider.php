<?php

namespace Laravel\Fortify\Contracts;

interface TwoFactorAuthenticationProvider
{
    /**
     * Generate a new secret key.
     *
     * @return string
     */
    public function generateSecretKey();

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
     * @param  string  $ts
     * @return int|bool
     */
    public function verify($secret, $code, $ts = null);

    /**
     * Provide current otp code based on secret.
     *
     * @param  string $secret
     * @return string
     */
    public function getCurrentOtp($secret);
}
