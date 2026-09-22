<?php

namespace Laravel\Fortify\Diagnostics;

trait ResolvesUserModel
{
    /**
     * Get the authentication model class used by Fortify's guard.
     *
     * @return class-string|null
     */
    protected function userModel(): ?string
    {
        $guard = config('fortify.guard', config('auth.defaults.guard', 'web'));
        $provider = config("auth.guards.{$guard}.provider", config('auth.defaults.provider'));

        $model = $provider
            ? config("auth.providers.{$provider}.model", config('auth.providers.users.model'))
            : config('auth.providers.users.model');

        return is_string($model) && class_exists($model) ? $model : null;
    }
}
