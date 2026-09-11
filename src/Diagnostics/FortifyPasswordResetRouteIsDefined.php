<?php

namespace Laravel\Fortify\Diagnostics;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Route;
use Laravel\Doctor\Diagnostic;
use Laravel\Doctor\Results\DiagnosticResult;
use Laravel\Doctor\Results\Link;
use Laravel\Doctor\Results\Message;
use Laravel\Fortify\Features;

class FortifyPasswordResetRouteIsDefined extends Diagnostic
{
    public string $name = 'Password reset route is defined';

    public string $group = 'fortify';

    /**
     * Get the diagnostic's named message definitions.
     *
     * @return array<string, string|Message>
     */
    protected function messages(): array
    {
        return [
            'not-used' => 'The password reset feature is disabled.',
            'defined' => 'The [password.reset] route is defined.',
            'customized' => 'Password reset notifications customize their URL or mail message.',
            'missing' => Message::make(
                summary: 'The password reset feature is enabled, but no [password.reset] route is defined.',
                remediation: 'Define a route named `password.reset` that shows your reset password view; reset emails link to it.',
            )->link(Link::docs('fortify', 'disabling-views-and-password-reset')),
        ];
    }

    /**
     * Run the diagnostic.
     */
    public function check(): DiagnosticResult
    {
        if (! Features::enabled(Features::resetPasswords())) {
            return $this->skip('not-used');
        }

        if (Route::has('password.reset')) {
            return $this->pass('defined');
        }

        if (ResetPassword::$createUrlCallback !== null || ResetPassword::$toMailCallback !== null) {
            return $this->pass('customized');
        }

        return $this->fail('missing');
    }
}
