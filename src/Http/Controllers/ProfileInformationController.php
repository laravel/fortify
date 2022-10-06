<?php

namespace Laravel\Fortify\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use Laravel\Fortify\Fortify;

class ProfileInformationController extends Controller
{
    /**
     * Update the user's profile information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Laravel\Fortify\Contracts\UpdatesUserProfileInformation  $updater
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,
                           UpdatesUserProfileInformation $updater)
    {
        $updater->update($request->user(), $request->all());

        return $request->wantsJson()
                    ? new JsonResponse('', 200)
                    : back()->with('status', Fortify::PROFILE_INFORMATION_UPDATED);
    }
}
