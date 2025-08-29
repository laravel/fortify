<?php

namespace Laravel\Fortify;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;

trait ManagesTwoFactorConfirmation
{
    /**
     * Validate the two-factor authentication state for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function validateTwoFactorAuthenticationState(Request $request): void
    {
        if (! Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm')) {
            return;
        }

        $currentTime = time();

        if ($this->twoFactorAuthenticationDisabled($request)) {
            $request->session()->put('two_factor_empty_at', $currentTime);
        }

        if ($this->hasJustBegunConfirmingTwoFactorAuthentication($request)) {
            $request->session()->put('two_factor_confirming_at', $currentTime);
        }

        if ($this->neverFinishedConfirmingTwoFactorAuthentication($request, $currentTime)) {
            app(DisableTwoFactorAuthentication::class)(Auth::user());

            $request->session()->put('two_factor_empty_at', $currentTime);
            $request->session()->remove('two_factor_confirming_at');
        }
    }

    /**
     * Determine if two-factor authentication is totally disabled.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function twoFactorAuthenticationDisabled(Request $request): bool
    {
        return is_null($request->user()->two_factor_secret) &&
            is_null($request->user()->two_factor_confirmed_at);
    }

    /**
     * Determine if two-factor authentication is just now being confirmed within the last request cycle.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function hasJustBegunConfirmingTwoFactorAuthentication(Request $request): bool
    {
        return ! is_null($request->user()->two_factor_secret) &&
            is_null($request->user()->two_factor_confirmed_at) &&
            $request->session()->has('two_factor_empty_at') &&
            is_null($request->session()->get('two_factor_confirming_at'));
    }

    /**
     * Determine if two-factor authentication was never totally confirmed once confirmation started.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $currentTime
     * @return bool
     */
    protected function neverFinishedConfirmingTwoFactorAuthentication(Request $request, int $currentTime): bool
    {
        return ! array_key_exists('code', $request->session()->getOldInput()) &&
            is_null($request->user()->two_factor_confirmed_at) &&
            $request->session()->get('two_factor_confirming_at', 0) != $currentTime;
    }
}
