<?php

namespace Laravel\Fortify\Tests\Controllers;

use Illuminate\Http\Request;
use Laravel\Fortify\ManagesTwoFactorConfirmation;

class ControllerWithManagesTwoFactorConfirmation
{
    use ManagesTwoFactorConfirmation;

    public function callValidateTwoFactorAuthenticationState(Request $request): void
    {
        $this->validateTwoFactorAuthenticationState($request);
    }
}
