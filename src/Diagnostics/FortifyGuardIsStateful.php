<?php

namespace Laravel\Fortify\Diagnostics;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Auth;
use Laravel\Doctor\Diagnostic;
use Laravel\Doctor\Results\DiagnosticResult;
use Laravel\Doctor\Results\Link;
use Laravel\Doctor\Results\Message;
use Laravel\Doctor\Support\Configured;
use Throwable;

class FortifyGuardIsStateful extends Diagnostic
{
    public string $name = 'Fortify guard is stateful';

    public string $group = 'fortify';

    /**
     * Get the diagnostic's named message definitions.
     *
     * @return array<string, string|Message>
     */
    protected function messages(): array
    {
        return [
            'stateful' => 'The Fortify guard [{guard}] is a stateful guard.',
            'undefined' => Message::make(
                summary: 'The Fortify guard [{guard}] is not defined in auth.guards.',
                remediation: 'Set fortify.guard to a session guard defined in config/auth.php.',
            )->link(Link::docs('fortify', 'authentication-guard')),
            'not-stateful' => Message::make(
                summary: 'The Fortify guard [{guard}] is not a stateful guard.',
                remediation: 'Set fortify.guard to a session guard; SPAs should use the web guard with Sanctum.',
            )->link(Link::docs('fortify', 'authentication-guard')),
        ];
    }

    /**
     * Run the diagnostic.
     */
    public function check(): DiagnosticResult
    {
        $guard = Configured::string('fortify.guard', 'web');

        try {
            $resolved = Auth::guard($guard);
        } catch (Throwable) {
            return $this->fail('undefined', ['guard' => $guard]);
        }

        if ($resolved instanceof StatefulGuard) {
            return $this->pass('stateful', ['guard' => $guard]);
        }

        return $this->fail('not-stateful', ['guard' => $guard]);
    }
}
