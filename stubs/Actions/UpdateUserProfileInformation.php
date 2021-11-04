<?php

namespace App\Actions\Fortify;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use Laravel\Fortify\Events\ProfileUpdated;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  mixed  $user
     * @param  array  $input
     * @return void
     */
    public function update($user, array $input)
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
        ])->validateWithBag('updateProfileInformation');

        if ($input['email'] !== $user->email) {
            $this->handleUserEmailChanged($user, $input);
        } else {
            $this->updateUsersProfile($user, $input);
            event(new ProfileUpdated($user));
        }
    }

    /**
     * Handles saving the user's information in the database.
     *
     * If `$mustVerify` is `true`, then `email_verified_at` will be set to `null`.
     *
     * @param  mixed  $user
     * @param  array  $input
     * @param  bool  $mustVerify
     * @return void
     */
    protected function updateUsersProfile($user, array $input, $mustVerify = false)
    {
        $data = [
            'name' => $input['name'],
            'email' => $input['email'],
        ];

        if ($mustVerify) {
            $data['email_verified_at'] = null;
        }

        $user->forceFill($data)->save();
    }

    /**
     * Update the given user's profile information.
     *
     * @param  mixed  $user
     * @param  array  $input
     * @return void
     */
    protected function handleUserEmailChanged($user, array $input)
    {
        $oldEmail = $user->email;
        $mustVerify = ($user instanceof MustVerifyEmail) ? true : false;

        $this->updateUsersProfile($user, $input, $mustVerify);

        if ($mustVerify) {
            $user->sendEmailVerificationNotification();
        }

        /**
         * Here you can notify the old email address that a change was made.
         *
         * @see https://laravel.com/docs/8.x/notifications
         */
        //Notification::route('mail', $oldEmail)->notify(new ProfileUpdatedNotification($user, $oldEmail));
    }
}
