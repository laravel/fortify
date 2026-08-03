<?php

namespace Laravel\Fortify\Diagnostics;

use Illuminate\Support\Facades\Schema;
use Laravel\Doctor\Diagnostic;
use Laravel\Doctor\EnvironmentMode;
use Laravel\Doctor\Results\DiagnosticResult;
use Laravel\Doctor\Results\Link;
use Laravel\Doctor\Results\Message;
use Laravel\Doctor\Support\Configured;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\Features;
use Laravel\Passkeys\Passkeys;
use Throwable;

class FortifyPasskeysAreConfigured extends Diagnostic
{
    use ResolvesUserModel;

    public string $name = 'Passkeys are configured';

    public string $group = 'fortify';

    /**
     * Get the diagnostic's named message definitions.
     *
     * @return array<string, string|Message>
     */
    protected function messages(): array
    {
        return [
            'not-used' => 'Passkeys are not used.',
            'no-model' => Message::make(
                summary: 'Passkeys are enabled, but the Fortify user model could not be resolved.',
                remediation: 'Configure the Fortify guard with a provider whose model is a valid class.',
            )->link(Link::docs('fortify', 'enabling-passkeys')),
            'no-app-url' => 'The application URL could not be parsed, so the passkeys configuration was not checked.',
            'database-unreachable' => 'The database could not be inspected for the passkeys table.',
            'configured' => 'The passkeys configuration matches the application URL.',
            'missing-contract' => Message::make(
                summary: 'Passkeys are enabled, but [{model}] does not implement PasskeyUser.',
                remediation: 'Implement `Laravel\Fortify\Contracts\PasskeyUser` and use the `PasskeyAuthenticatable` trait on [{model}].',
            )->link(Link::docs('fortify', 'enabling-passkeys')),
            'insecure-url' => Message::make(
                summary: 'Passkeys require a secure context, but the application URL [{url}] is not HTTPS.',
                remediation: 'Set APP_URL to an https URL; WebAuthn does not work over plain HTTP.',
            )->link(Link::docs('fortify', 'enabling-passkeys')),
            'mismatched-relying-party' => Message::make(
                summary: 'The passkeys relying party [{id}] does not match the application host [{host}].',
                remediation: 'Set fortify.passkeys.relying_party_id to [{host}] or a parent domain of it.',
            )->link(Link::docs('fortify', 'enabling-passkeys')),
            'origin-not-allowed' => Message::make(
                summary: 'The application URL [{url}] is not listed in the passkeys allowed origins.',
                remediation: 'Add [{url}] to fortify.passkeys.allowed_origins.',
            )->link(Link::docs('fortify', 'enabling-passkeys')),
            'missing-table' => Message::make(
                summary: 'The [{table}] table required by passkeys does not exist.',
                remediation: 'Publish the passkeys migrations and run `php artisan migrate`.',
            )->link(Link::docs('fortify', 'enabling-passkeys')),
        ];
    }

    /**
     * Run the diagnostic.
     */
    public function check(): DiagnosticResult
    {
        if (! Features::enabled(Features::passkeys())) {
            return $this->skip('not-used');
        }

        $model = $this->userModel();

        if ($model === null) {
            return $this->fail('no-model');
        }

        if (! is_subclass_of($model, PasskeyUser::class)) {
            return $this->fail('missing-contract', ['model' => $model]);
        }

        $url = rtrim(Configured::string('app.url', ''), '/');
        $host = parse_url($url, PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return $this->skip('no-app-url');
        }

        if ($this->requiresSecureUrl($host) && parse_url($url, PHP_URL_SCHEME) !== 'https') {
            return $this->fail('insecure-url', ['url' => $url]);
        }

        $relyingParty = Configured::string('fortify.passkeys.relying_party_id', '');

        if ($relyingParty !== '' && $host !== $relyingParty && ! str_ends_with($host, '.'.$relyingParty)) {
            return $this->fail('mismatched-relying-party', ['id' => $relyingParty, 'host' => $host]);
        }

        if (! $this->originIsAllowed($url)) {
            return $this->fail('origin-not-allowed', ['url' => $url]);
        }

        $passkey = new (Passkeys::passkeyModel());

        try {
            $schema = Schema::connection($passkey->getConnectionName());

            if (! $schema->hasTable($passkey->getTable())) {
                return $this->fail('missing-table', ['table' => $passkey->getTable()]);
            }
        } catch (Throwable) {
            return $this->skip('database-unreachable');
        }

        return $this->pass('configured');
    }

    /**
     * Determine whether the application URL must use HTTPS for passkeys.
     */
    private function requiresSecureUrl(string $host): bool
    {
        return EnvironmentMode::current()->isProduction()
            && ! in_array($host, ['localhost', '127.0.0.1'], true);
    }

    /**
     * Determine whether the application URL is an allowed passkey origin.
     */
    private function originIsAllowed(string $url): bool
    {
        foreach ((array) config('fortify.passkeys.allowed_origins', []) as $origin) {
            if (is_string($origin) && rtrim($origin, '/') === $url) {
                return true;
            }
        }

        return false;
    }
}
