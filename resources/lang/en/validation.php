<?php

/*
|--------------------------------------------------------------------------
| Laravel Fortify Validation Language Lines
|--------------------------------------------------------------------------
|
| The following language lines contain the default error messages used by
| the validator class inside Laravel Fortify. Feel free to tweak each
| of these messages here.
|
*/

return [
    'password' => [
        'min' => 'The :attribute must be at least :length characters.',
        'uppercase' => 'The :attribute must be at least :length characters and contain at least one uppercase character.',
        'numeric' => 'The :attribute must be at least :length characters and contain at least one number.',
        'special_character' => 'The :attribute must be at least :length characters and contain at least one special character.',
        'uppercase_or_numeric' => 'The :attribute must be at least :length characters and contain at least one uppercase character and one number.',
        'uppercase_or_special_character' => 'The :attribute must be at least :length characters and contain at least one uppercase character and one special character.',
        'uppercase_or_numeric_or_special_character' => 'The :attribute must be at least :length characters and contain at least one uppercase character, one number, and one special character.',
    ]
];
