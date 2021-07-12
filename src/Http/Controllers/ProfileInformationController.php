<?php

namespace Laravel\Fortify\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravel\Fortify\Contracts\ProfileInformationUpdateResponse;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class ProfileInformationController extends Controller
{
    /**
     * Update the user's profile information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Laravel\Fortify\Contracts\UpdatesUserProfileInformation  $updater
     * @return \Laravel\Fortify\Contracts\ProfileInformationUpdateResponse
     */
    public function update(
        Request $request,
        UpdatesUserProfileInformation $updater
    ) {
        $updater->update($request->user(), $request->all());

        return app(ProfileInformationUpdateResponse::class);
    }
}
