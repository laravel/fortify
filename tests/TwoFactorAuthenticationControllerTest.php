<?php

namespace Laravel\Fortify\Tests;

use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Fortify\Events\TwoFactorAuthenticationConfirmed;
use Laravel\Fortify\Events\TwoFactorAuthenticationDisabled;
use Laravel\Fortify\Events\TwoFactorAuthenticationEnabled;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use Orchestra\Testbench\Attributes\ResetRefreshDatabaseState;
use Orchestra\Testbench\Attributes\WithMigration;
use PragmaRX\Google2FA\Google2FA;

#[WithMigration]
class TwoFactorAuthenticationControllerTest extends OrchestraTestCase
{
    use RefreshDatabase;

    public function test_two_factor_authentication_can_be_enabled()
    {
        Event::fake();

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

    #[ResetRefreshDatabaseState]
    public function test_calling_two_factor_authentication_endpoint_will_not_overwrite_without_force_parameter()
    {
        Event::fake();

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

        $old_value = $user->two_factor_secret;

        $response = $this->withoutExceptionHandling()->actingAs($user)->postJson(
            '/user/two-factor-authentication'
        );

        $response->assertStatus(200);

        $this->assertNotNull($user->two_factor_secret);
        $this->assertNotNull($user->two_factor_recovery_codes);
        $this->assertEquals($old_value, $user->fresh()->two_factor_secret);
        $this->assertNull($user->two_factor_confirmed_at);
        $this->assertIsArray(json_decode(decrypt($user->two_factor_recovery_codes), true));
        $this->assertNotNull($user->twoFactorQrCodeSvg());
    }

    #[ResetRefreshDatabaseState]
    public function test_calling_two_factor_authentication_endpoint_will_overwrite_with_force_parameter()
    {
        Event::fake();

        $user = TestTwoFactorAuthenticationUser::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => bcrypt('secret'),
        ]);

        $response = $this->withoutExceptionHandling()->actingAs($user)->postJson(
            '/user/two-factor-authentication',
            [
                'force' => true,
            ]
        );

        $response->assertStatus(200);

        Event::assertDispatched(TwoFactorAuthenticationEnabled::class);

        $user = $user->fresh();

        $old_value = $user->two_factor_secret;

        $response = $this->withoutExceptionHandling()->actingAs($user)->postJson(
            '/user/two-factor-authentication',
            [
                'force' => true,
            ]
        );

        $response->assertStatus(200);

        $user = $user->fresh();

        $this->assertNotNull($user->two_factor_secret);
        $this->assertNotNull($user->two_factor_recovery_codes);
        $this->assertNotEquals($old_value, $user->fresh()->two_factor_secret);
        $this->assertNull($user->two_factor_confirmed_at);
        $this->assertIsArray(json_decode(decrypt($user->two_factor_recovery_codes), true));
        $this->assertNotNull($user->twoFactorQrCodeSvg());
    }

    public function test_two_factor_authentication_secret_key_can_be_retrieved()
    {
        Event::fake();

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

    #[DefineEnvironment('withConfirmedTwoFactorAuthentication')]
    #[ResetRefreshDatabaseState]
    public function test_two_factor_authentication_can_be_confirmed()
    {
        Event::fake();

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

    #[DefineEnvironment('withConfirmedTwoFactorAuthentication')]
    #[ResetRefreshDatabaseState]
    public function test_two_factor_authentication_can_not_be_confirmed_with_invalid_code()
    {
        Event::fake();

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
}

class TestTwoFactorAuthenticationUser extends User
{
    use TwoFactorAuthenticatable;

    protected $table = 'users';
}
