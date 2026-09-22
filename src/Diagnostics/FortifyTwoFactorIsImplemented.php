<?php

namespace Laravel\Fortify\Diagnostics;

use Illuminate\Support\Facades\Schema;
use Laravel\Doctor\Diagnostic;
use Laravel\Doctor\Results\DiagnosticResult;
use Laravel\Doctor\Results\Link;
use Laravel\Doctor\Results\Message;
use Laravel\Doctor\Support\Details;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Throwable;

class FortifyTwoFactorIsImplemented extends Diagnostic
{
    use ResolvesUserModel;

    public string $name = 'Two-factor authentication is implemented';

    public string $group = 'fortify';

    /**
     * The columns added by Fortify's two-factor migration.
     *
     * @var list<string>
     */
    protected array $columns = [
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the diagnostic's named message definitions.
     *
     * @return array<string, string|Message>
     */
    protected function messages(): array
    {
        return [
            'not-used' => 'Two-factor authentication is not used.',
            'no-model' => 'The Fortify user model could not be resolved.',
            'database-unreachable' => 'The database could not be inspected for the two-factor columns.',
            'implemented' => 'Two-factor authentication is enabled and [{model}] is configured.',
            'missing-trait' => Message::make(
                summary: 'Two-factor authentication is enabled, but [{model}] does not use the TwoFactorAuthenticatable trait.',
                remediation: 'Use the `Laravel\Fortify\TwoFactorAuthenticatable` trait on [{model}].',
            )->link(Link::docs('fortify', 'two-factor-authentication')),
            'missing-columns' => Message::make(
                summary: 'The [{table}] table is missing the two-factor authentication columns.',
                remediation: 'Run `php artisan migrate` to add the two-factor columns published by `fortify:install`.',
            )->link(Link::docs('fortify', 'two-factor-authentication')),
        ];
    }

    /**
     * Run the diagnostic.
     */
    public function check(): DiagnosticResult
    {
        if (! Features::enabled(Features::twoFactorAuthentication())) {
            return $this->skip('not-used');
        }

        $model = $this->userModel();

        if ($model === null) {
            return $this->skip('no-model');
        }

        if (! in_array(TwoFactorAuthenticatable::class, class_uses_recursive($model), true)) {
            return $this->fail('missing-trait', ['model' => $model]);
        }

        $instance = new $model;

        try {
            $schema = Schema::connection($instance->getConnectionName());

            $missing = array_values(array_filter(
                $this->requiredColumns(),
                fn (string $column): bool => ! $schema->hasColumn($instance->getTable(), $column),
            ));
        } catch (Throwable) {
            return $this->skip('database-unreachable');
        }

        if ($missing !== []) {
            return $this->fail('missing-columns', ['table' => $instance->getTable()])
                ->withDetails(Details::bullets($missing));
        }

        return $this->pass('implemented', ['model' => $model]);
    }

    /**
     * Get the columns required by the configured two-factor options.
     *
     * @return list<string>
     */
    private function requiredColumns(): array
    {
        return Fortify::confirmsTwoFactorAuthentication()
            ? [...$this->columns, 'two_factor_confirmed_at']
            : $this->columns;
    }
}
