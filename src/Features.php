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
     * @param array $options
     * @return string
     */
    public static function registration(array $options = [])
    {
        if(! empty($options) && isset($options['enabled'])) {
            return $options['enabled'] === true ? 'registration' : null;
        }

        return 'registration';
    }

    /**
     * Enable the password reset feature.
     *
     * @param array $options
     * @return string|null
     */
    public static function resetPasswords(array $options = [])
    {
        if(! empty($options) && isset($options['enabled'])) {
            return $options['enabled'] === true ? 'reset-passwords' : null;
        }

        return 'reset-passwords';
    }

    /**
     * Enable the email verification feature.
     *
     * @param array $options
     * @return string|null
     */
    public static function emailVerification(array $options = [])
    {
        if(! empty($options) && isset($options['enabled'])) {
            return $options['enabled'] === true ? 'email-verification' : null;
        }

        return 'email-verification';
    }

    /**
     * Enable the update profile information feature.
     *
     * @param array $options
     * @return string|null
     */
    public static function updateProfileInformation(array $options = [])
    {
        if(! empty($options) && isset($options['enabled'])) {
            return $options['enabled'] === true ? 'update-profile-information' : null;
        }

        return 'update-profile-information';
    }

    /**
     * Enable the update password feature.
     *
     * @param array $options
     * @return string|null
     */
    public static function updatePasswords(array $options = [])
    {
        if(! empty($options) && isset($options['enabled'])) {
            return $options['enabled'] === true ? 'update-passwords' : null;
        }

        return 'update-passwords';
    }

    /**
     * Enable the two factor authentication feature.
     *
     * @param array $options
     * @return string
     */
    public static function twoFactorAuthentication(array $options = [])
    {
        if (! empty($options)) {
            if(isset($options['enabled']) && $options['enabled'] === false) {
                return null;
            }

            static::$featureOptions['two-factor-authentication'] = $options;
        }

        return 'two-factor-authentication';
    }
}
