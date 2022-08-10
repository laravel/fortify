<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Validation\Rule;

trait UsernameValidationRules
{
    /**
     * Get the validation rules used to validate usernames.
     *
     * @return array
     */
    protected function usernameRules()
    {
        return [
            'required',
            'string',
            'email',
            'max:255',
            Rule::unique(User::class),
        ];
    }
}
