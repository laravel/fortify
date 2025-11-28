<?php

namespace Laravel\Fortify\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\ProfileInformationUpdatedResponse;
use Laravel\Fortify\Contracts\ProfileViewResponse;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use Laravel\Fortify\Fortify;

class ProfileInformationController extends Controller
{
    /**
     * Show the profile view.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Laravel\Fortify\Contracts\ProfileViewResponse
     */
    public function create( Request $request ): ProfileViewResponse
    {
        return app( ProfileViewResponse::class );
    }

    /**
     * Update the user's profile information.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Laravel\Fortify\Contracts\UpdatesUserProfileInformation $updater
     *
     * @return \Laravel\Fortify\Contracts\ProfileInformationUpdatedResponse
     */
    public function update( Request $request,
        UpdatesUserProfileInformation $updater )
    {
        if ( config( 'fortify.lowercase_usernames' ) && $request->has( Fortify::username() ) )
        {
            $request->merge( [
                                 Fortify::username() => Str::lower( $request->{Fortify::username()} ),
                             ] );
        }

        $updater->update( $request->user(), $request->all() );

        return app( ProfileInformationUpdatedResponse::class );
    }


    /**
     * Destroy the user's profile.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy( Request $request )
    {
        $request->validateWithBag( 'userDeletion', [
            'password' => [ 'required', 'current_password' ],
        ] );

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to( '/' );
    }
}
