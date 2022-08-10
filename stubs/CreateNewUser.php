<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Fortify\Contracts\UserRegistrationRequest;
use Laravel\Fortify\Fortify;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  \Laravel\Fortify\Contracts\UserRegistrationRequest  $request
     * @return mixed
     */
    public function create(UserRegistrationRequest $request)
    {
        $request->validated();

        return User::create([
            'name' => $request->name(),
            Fortify::username() => $request->username(),
            'password' => Hash::make($request->password()),
        ]);
    }
}
