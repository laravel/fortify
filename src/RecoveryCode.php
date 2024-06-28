<?php

namespace Laravel\Fortify;

use Illuminate\Support\Str;

class RecoveryCode
{
    /**
     * The callback that should be used to generate a recovery code.
     *
     * @var (\Closure(mixed, string): string)|null
     */
    public static $generateRecoveryCodeCallback;

    /**
     * Generate a new recovery code.
     *
     * @return string
     */
    public static function generate()
    {
        if (static::$generateRecoveryCodeCallback) {
            return call_user_func(static::$generateRecoveryCodeCallback);
        }

        return Str::random(10).'-'.Str::random(10);
    }

    /**
     * Set a callback that should be used when generating a recovery code.
     *
     * @param  \Closure(mixed, string): string  $callback
     * @return void
     */
    public static function generateRecoveryCodeUsing($callback)
    {
        static::$generateRecoveryCodeCallback = $callback;
    }
}
