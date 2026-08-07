<?php

namespace Laravel\Fortify;

use Illuminate\Support\Str;

class RecoveryCode
{
    /**
     * Generate a new recovery code.
     *
     * @return string
     */
    public static function generate()
    {
        if (Fortify::$recoveryCodeGenerator) {
            return call_user_func(Fortify::$recoveryCodeGenerator);
        }

        return Str::random(10).'-'.Str::random(10);
    }
}
