<?php

namespace App\Actions\Fortify;

use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Fortify\Contracts\UserRegistrationRequest;

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
            'email' => $request->email(),
            'password' => Hash::make($request->password()),
        ]);
    }
}
