<?php

namespace Laravel\Fortify;

class RouteName
{
    /**
     * Get the full route name for the given name if a named prefix is used.
     *
     * @param  string  $routeName
     * @return string
     */
    public static function for(string $routeName)
    {
        if (! config('fortify.name_prefix')) {
            return $routeName;
        }

        return config('fortify.name_prefix').'.'.$routeName;
    }
}
