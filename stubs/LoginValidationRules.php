<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Fortify;
use Laravel\Fortify\Rules\Password;

trait LoginValidationRules
{
    /**
     * Get the login validation rules.
     *
     * @return array
     */
    protected function loginRules()
    {
        return [
            Fortify::username() => ['required', 'string'],
            'password' => ['required', 'string', new Password],
        ];
    }
}
