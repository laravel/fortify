<?php

namespace Laravel\Fortify\Tests;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Event;
use Laravel\Fortify\Events\TwoFactorAuthenticationConfirmed;
use Laravel\Fortify\Events\TwoFactorAuthenticationDisabled;
use Laravel\Fortify\Events\TwoFactorAuthenticationEnabled;
use Laravel\Fortify\Features;
use Laravel\Fortify\FortifyServiceProvider;
use Laravel\Fortify\TwoFactorAuthenticatable;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthenticationControllerTest extends OrchestraTestCase
{
    public function test_two_factor_authentication_can_be_enabled()
    {
        Event::fake();

        $this->loadLaravelMigrations(['--database' => 'testbench']);
        $this->artisan('migrate', ['--database' => 'testbench'])->run();

        $user = TestTwoFactorAuthenticationUser::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => bcrypt('secret'),
        ]);

        $response = $this->withoutExceptionHandling()->actingAs($user)->postJson(
            '/user/two-factor-authentication'
        );

        $response->assertStatus(200);

        Event::assertDispatched(TwoFactorAuthenticationEnabled::class);

        $user = $user->fresh();

        $this->assertNotNull($user->two_factor_secret);
        $this->assertNotNull($user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_confirmed_at);
        $this->assertIsArray(json_decode(decrypt($user->two_factor_recovery_codes), true));
        $this->assertNotNull($user->twoFactorQrCodeSvg());
    }

    public function test_two_factor_authentication_secret_key_can_be_retrieved()
    {
        Event::fake();

        $this->loadLaravelMigrations(['--database' => 'testbench']);
        $this->artisan('migrate', ['--database' => 'testbench'])->run();

        $user = TestTwoFactorAuthenticationUser::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => bcrypt('secret'),
            'two_factor_secret' => encrypt('foo'),
        ]);

        $response = $this->withoutExceptionHandling()->actingAs($user)->getJson(
            '/user/two-factor-secret-key'
        );

        $response->assertStatus(200);

        $this->assertEquals('foo', $response->original['secretKey']);
    }

    public function test_two_factor_authentication_can_be_confirmed()
    {
        Event::fake();

        app('config')->set('fortify.features', [
            Features::twoFactorAuthentication(['confirm' => true]),
        ]);

        $this->loadLaravelMigrations(['--database' => 'testbench']);
        $this->artisan('migrate', ['--database' => 'testbench'])->run();

        $tfaEngine = app(Google2FA::class);
        $userSecret = $tfaEngine->generateSecretKey();
        $validOtp = $tfaEngine->getCurrentOtp($userSecret);

        $user = TestTwoFactorAuthenticationUser::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => bcrypt('secret'),
            'two_factor_secret' => encrypt($userSecret),
            'two_factor_confirmed_at' => null,
        ]);

        $response = $this->withoutExceptionHandling()->actingAs($user)->postJson(
            '/user/confirmed-two-factor-authentication', ['code' => $validOtp],
        );

        $response->assertStatus(200);

        Event::assertDispatched(TwoFactorAuthenticationConfirmed::class);

        $user = $user->fresh();

        $this->assertNotNull($user->two_factor_confirmed_at);
        $this->assertTrue($user->hasEnabledTwoFactorAuthentication());

        // Ensure two factor authentication not considered enabled if not confirmed...
        $user->forceFill(['two_factor_confirmed_at' => null])->save();

        $this->assertFalse($user->hasEnabledTwoFactorAuthentication());
    }

    public function test_two_factor_authentication_can_not_be_confirmed_with_invalid_code()
    {
        Event::fake();

        app('config')->set('fortify.features', [
            Features::twoFactorAuthentication(['confirm' => true]),
        ]);

        $this->loadLaravelMigrations(['--database' => 'testbench']);
        $this->artisan('migrate', ['--database' => 'testbench'])->run();

        $tfaEngine = app(Google2FA::class);
        $userSecret = $tfaEngine->generateSecretKey();

        $user = TestTwoFactorAuthenticationUser::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => bcrypt('secret'),
            'two_factor_secret' => encrypt($userSecret),
            'two_factor_confirmed_at' => null,
        ]);

        $response = $this->withExceptionHandling()->actingAs($user)->postJson(
            '/user/confirmed-two-factor-authentication', ['code' => 'invalid-otp'],
        );

        $response->assertStatus(422);

        Event::assertNotDispatched(TwoFactorAuthenticationConfirmed::class);

        $user = $user->fresh();

        $this->assertNull($user->two_factor_confirmed_at);
    }

    public function test_two_factor_authentication_can_be_disabled()
    {
        Event::fake();

        $this->loadLaravelMigrations(['--database' => 'testbench']);
        $this->artisan('migrate', ['--database' => 'testbench'])->run();

        $user = TestTwoFactorAuthenticationUser::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => bcrypt('secret'),
            'two_factor_secret' => encrypt('foo'),
            'two_factor_recovery_codes' => encrypt(json_encode([])),
        ]);

        $response = $this->withoutExceptionHandling()->actingAs($user)->deleteJson(
            '/user/two-factor-authentication'
        );

        $response->assertStatus(200);

        Event::assertDispatched(TwoFactorAuthenticationDisabled::class);

        $user->fresh();

        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_recovery_codes);
    }

    protected function getPackageProviders($app)
    {
        return [FortifyServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['migrator']->path(__DIR__.'/../database/migrations');

        $app['config']->set('database.default', 'testbench');

        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }
}

class TestTwoFactorAuthenticationUser extends User
{
    use TwoFactorAuthenticatable;

    protected $table = 'users';
}
