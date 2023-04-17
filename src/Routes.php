<?php

namespace Laravel\Fortify;

class Routes
{
    /**
     * Get custom route path from config for routes.
     *
     * @param  string  $route
     * @param  string  $default
     * @return string
     */
    public static function path(string $route, string $default)
    {
        return config('fortify.route_paths.'.$route) ?? $default;
    }
}
