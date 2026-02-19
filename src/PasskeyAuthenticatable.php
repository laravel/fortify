<?php

namespace Laravel\Fortify;

/**
 * @phpstan-require-implements \Laravel\Fortify\Contracts\PasskeyUser
 */
trait PasskeyAuthenticatable
{
    use \Laravel\Passkeys\PasskeyAuthenticatable;
}
