<?php

namespace Laravel\Fortify;

use Laravel\Passkeys\PasskeyAuthenticatable as BasePasskeyAuthenticatable;

/**
 * @phpstan-require-implements \Laravel\Fortify\Contracts\PasskeyUser
 */
trait PasskeyAuthenticatable
{
    use BasePasskeyAuthenticatable;
}
