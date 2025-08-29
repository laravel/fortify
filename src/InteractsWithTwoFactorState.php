<?php

namespace Laravel\Fortify;

use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;

/**
 * @mixin \Illuminate\Foundation\Http\FormRequest
 */
trait InteractsWithTwoFactorState
{
    /**
     * Ensure the two-factor authentication state is valid and handle transitions.
     *
     * @return void
     */
    public function ensureStateIsValid()
    {
        if (! Fortify::confirmsTwoFactorAuthentication()) {
            return;
        }

        $currentTime = time();

        if (! $this->user()->hasEnabledTwoFactorAuthentication()) {
            $this->session()->put('two_factor_empty_at', $currentTime);
        }

        if ($this->hasJustBegunConfirmingTwoFactorAuthentication()) {
            $this->session()->put('two_factor_confirming_at', $currentTime);
        }

        if ($this->neverFinishedConfirmingTwoFactorAuthentication($currentTime)) {
            app(DisableTwoFactorAuthentication::class)($this->user());

            $this->session()->put('two_factor_empty_at', $currentTime);
            $this->session()->remove('two_factor_confirming_at');
        }
    }

    /**
     * Determine if two-factor authentication is just now being confirmed within the last request cycle.
     *
     * @return bool
     */
    protected function hasJustBegunConfirmingTwoFactorAuthentication()
    {
        return ! is_null($this->user()->two_factor_secret) &&
            is_null($this->user()->two_factor_confirmed_at) &&
            $this->session()->has('two_factor_empty_at') &&
            is_null($this->session()->get('two_factor_confirming_at'));
    }

    /**
     * Determine if two-factor authentication was never totally confirmed once confirmation started.
     *
     * @param  int  $currentTime
     * @return bool
     */
    protected function neverFinishedConfirmingTwoFactorAuthentication(int $currentTime)
    {
        return ! $this->session()->hasOldInput('code') &&
            is_null($this->user()->two_factor_confirmed_at) &&
            $this->session()->get('two_factor_confirming_at', 0) != $currentTime;
    }
}
