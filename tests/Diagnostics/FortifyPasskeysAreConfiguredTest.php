<?php

namespace Laravel\Fortify\Tests\Diagnostics;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Schema;
use Laravel\Doctor\Results\Status;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\Diagnostics\FortifyPasskeysAreConfigured;
use Laravel\Fortify\Features;
use Laravel\Fortify\PasskeyAuthenticatable;

class FortifyPasskeysAreConfiguredTest extends DiagnosticsTestCase
{
    protected function withPasskeyUser(array $config = [], bool $withTable = true): void
    {
        config(array_merge([
            'fortify.features' => [Features::passkeys()],
            'auth.providers.users.model' => PasskeyCapableUser::class,
        ], $config));

        if ($withTable) {
            Schema::create('passkeys', function ($table) {
                $table->id();
            });
        }
    }

    public function test_diagnostic_skips_when_the_feature_is_not_used()
    {
        config(['fortify.features' => []]);

        $result = (new FortifyPasskeysAreConfigured)->check();

        $this->assertSame(Status::Skip, $result->status);
        $this->assertSame('Passkeys are not used.', $result->summary);
    }

    public function test_diagnostic_fails_when_the_model_is_missing_the_contract()
    {
        config(['fortify.features' => [Features::passkeys()]]);

        $result = (new FortifyPasskeysAreConfigured)->check();

        $this->assertSame(Status::Fail, $result->status);
        $this->assertStringContainsString('does not implement PasskeyUser', $result->summary);
    }

    public function test_diagnostic_fails_when_the_user_model_cannot_be_resolved()
    {
        config([
            'fortify.features' => [Features::passkeys()],
            'auth.providers.users.model' => 'App\\Models\\MissingUser',
        ]);

        $result = (new FortifyPasskeysAreConfigured)->check();

        $this->assertSame(Status::Fail, $result->status);
        $this->assertStringContainsString('could not be resolved', $result->summary);
    }

    public function test_diagnostic_passes_when_the_configuration_matches()
    {
        $this->withPasskeyUser([
            'app.url' => 'https://example.com',
            'fortify.passkeys.relying_party_id' => 'example.com',
            'fortify.passkeys.allowed_origins' => ['https://example.com'],
        ]);

        $result = (new FortifyPasskeysAreConfigured)->check();

        $this->assertSame(Status::Pass, $result->status);
    }

    public function test_diagnostic_allows_a_parent_domain_relying_party()
    {
        $this->withPasskeyUser([
            'app.url' => 'https://app.example.com',
            'fortify.passkeys.relying_party_id' => 'example.com',
            'fortify.passkeys.allowed_origins' => ['https://app.example.com'],
        ]);

        $result = (new FortifyPasskeysAreConfigured)->check();

        $this->assertSame(Status::Pass, $result->status);
    }

    public function test_diagnostic_fails_when_the_application_url_is_not_https_in_production()
    {
        $this->withPasskeyUser([
            'app.url' => 'http://example.com',
            'fortify.passkeys.relying_party_id' => 'example.com',
            'fortify.passkeys.allowed_origins' => ['http://example.com'],
        ]);

        $result = (new FortifyPasskeysAreConfigured)->check();

        $this->assertSame(Status::Fail, $result->status);
        $this->assertStringContainsString('not HTTPS', $result->summary);
    }

    public function test_diagnostic_fails_when_the_relying_party_does_not_match()
    {
        $this->withPasskeyUser([
            'app.url' => 'https://example.com',
            'fortify.passkeys.relying_party_id' => 'other.com',
            'fortify.passkeys.allowed_origins' => ['https://example.com'],
        ]);

        $result = (new FortifyPasskeysAreConfigured)->check();

        $this->assertSame(Status::Fail, $result->status);
        $this->assertSame('The passkeys relying party [other.com] does not match the application host [example.com].', $result->summary);
    }

    public function test_diagnostic_fails_when_the_application_url_is_not_an_allowed_origin()
    {
        $this->withPasskeyUser([
            'app.url' => 'https://example.com',
            'fortify.passkeys.relying_party_id' => 'example.com',
            'fortify.passkeys.allowed_origins' => ['https://other.com'],
        ]);

        $result = (new FortifyPasskeysAreConfigured)->check();

        $this->assertSame(Status::Fail, $result->status);
        $this->assertStringContainsString('not listed in the passkeys allowed origins', $result->summary);
    }

    public function test_diagnostic_fails_when_the_passkeys_table_is_missing()
    {
        $this->withPasskeyUser([
            'app.url' => 'https://example.com',
            'fortify.passkeys.relying_party_id' => 'example.com',
            'fortify.passkeys.allowed_origins' => ['https://example.com'],
        ], withTable: false);

        $result = (new FortifyPasskeysAreConfigured)->check();

        $this->assertSame(Status::Fail, $result->status);
        $this->assertStringContainsString('[passkeys] table', $result->summary);
    }
}

class PasskeyCapableUser extends User implements PasskeyUser
{
    use PasskeyAuthenticatable;

    protected $table = 'users';
}
