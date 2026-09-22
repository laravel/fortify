<?php

namespace Laravel\Fortify\Diagnostics;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Route;
use Laravel\Doctor\Diagnostic;
use Laravel\Doctor\Results\DiagnosticResult;
use Laravel\Doctor\Results\Link;
use Laravel\Doctor\Results\Message;
use Laravel\Fortify\Features;

class FortifyEmailVerificationIsImplemented extends Diagnostic
{
    use ResolvesUserModel;

    public string $name = 'Email verification is implemented';

    public string $group = 'fortify';

    /**
     * Get the diagnostic's named message definitions.
     *
     * @return array<string, string|Message>
     */
    protected function messages(): array
    {
        return [
            'not-used' => 'Email verification is not used.',
            'no-model' => 'The Fortify user model could not be resolved.',
            'implemented' => 'Email verification is enabled and [{model}] implements MustVerifyEmail.',
            'routes-defined' => 'Email verification routes are defined outside Fortify for [{model}].',
            'missing-interface' => Message::make(
                summary: 'Email verification is enabled, but [{model}] does not implement MustVerifyEmail.',
                remediation: 'Implement `Illuminate\Contracts\Auth\MustVerifyEmail` on [{model}] so verification emails are sent.',
            )->link(Link::docs('fortify', 'email-verification')),
            'missing-feature' => Message::make(
                summary: '[{model}] implements MustVerifyEmail, but the email verification feature is disabled.',
                remediation: 'Add `Features::emailVerification()` to fortify.features so the verification routes are registered.',
            )->link(Link::docs('fortify', 'email-verification')),
        ];
    }

    /**
     * Run the diagnostic.
     */
    public function check(): DiagnosticResult
    {
        $enabled = Features::enabled(Features::emailVerification());
        $model = $this->userModel();

        if ($model === null) {
            return $enabled ? $this->skip('no-model') : $this->skip('not-used');
        }

        $verifies = is_subclass_of($model, MustVerifyEmail::class);

        return match (true) {
            $enabled && $verifies => $this->pass('implemented', ['model' => $model]),
            $enabled => $this->fail('missing-interface', ['model' => $model]),
            $verifies && Route::has(['verification.verify', 'verification.notice']) => $this->pass('routes-defined', ['model' => $model]),
            $verifies => $this->warn('missing-feature', ['model' => $model]),
            default => $this->skip('not-used'),
        };
    }
}
