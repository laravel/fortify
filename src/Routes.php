<?php

namespace Laravel\Fortify;

class Routes
{
    /**
     * Get custom route name from config for routes
     *
     * @param  string  $route
     * @param  string  $default
     * @return string
     */
    public static function name(string $route, string $default)
    {
        return config('fortify.route_names.' . $route) ?? $default;
    }
}
