<?php

namespace Laravel\Fortify;

use Illuminate\Support\Arr;

class Features
{
    /**
     * The options enabled for a given feature.
     *
     * @var array
     */
    protected static $featureOptions = [];

    /**
     * Determine if the given feature is enabled.
     *
     * @param  string  $feature
     * @return bool
     */
    public static function enabled(string $feature)
    {
        return in_array($feature, config('fortify.features', []));
    }

    /**
     * Determine if the feature is enabled and has a given option enabled.
     *
     * @param  string  $feature
     * @param  string  $option
     * @return bool
     */
    public static function optionEnabled(string $feature, string $option)
    {
        return static::enabled($feature) &&
               Arr::get(static::$featureOptions, $feature.'.'.$option) === true;
    }

    /**
     * Determine if the application is using any features that require "profile" management.
     *
     * @return bool
     */
    public static function hasProfileFeatures()
    {
        return static::enabled(static::updateProfileInformation()) ||
               static::enabled(static::updatePasswords()) ||
               static::enabled(static::twoFactorAuthentication());
    }

    /**
     * Determine if the application can update a user's profile information.
     *
     * @return bool
     */
    public static function canUpdateProfileInformation()
    {
        return static::enabled(static::updateProfileInformation());
    }

    /**
     * Determine if the application is using any security profile features.
     *
     * @return bool
     */
    public static function hasSecurityFeatures()
    {
        return static::enabled(static::updatePasswords()) ||
               static::canManageTwoFactorAuthentication();
    }

    /**
     * Determine if the application can manage two factor authentication.
     *
     * @return bool
     */
    public static function canManageTwoFactorAuthentication()
    {
        return static::enabled(static::twoFactorAuthentication());
    }

    /**
     * Enable the registration feature.
     *
     * @return string
     */
    public static function registration()
    {
        return 'registration';
    }

    /**
     * Enable the password reset feature.
     *
     * @return string
     */
    public static function resetPasswords()
    {
        return 'reset-passwords';
    }

    /**
     * Enable the email verification feature.
     *
     * @return string
     */
    public static function emailVerification()
    {
        return 'email-verification';
    }

    /**
     * Enable the update profile information feature.
     *
     * @return string
     */
    public static function updateProfileInformation()
    {
        return 'update-profile-information';
    }

    /**
     * Enable the update password feature.
     *
     * @return string
     */
    public static function updatePasswords()
    {
        return 'update-passwords';
    }

    /**
     * Enable the two factor authentication feature.
     *
     * @param  array  $options
     * @return string
     */
    public static function twoFactorAuthentication(array $options = [])
    {
        if (! empty($options)) {
            static::$featureOptions['two-factor-authentication'] = $options;
        }

        return 'two-factor-authentication';
    }
}
