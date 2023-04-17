<?php

namespace Laravel\Fortify;

class RoutePath
{
    /**
     * Get the route path for the given route name.
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
