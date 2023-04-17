<?php

namespace Laravel\Fortify;

class RoutePath
{
    /**
     * Get custom route path from config for routes.
     *
     * @param  string  $routeName
     * @param  string  $default
     * @return string
     */
    public static function for(string $routeName, string $default)
    {
        return config('fortify.paths.'.$routeName) ?? $default;
    }
}
