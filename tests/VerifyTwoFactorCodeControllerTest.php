<?php

namespace Laravel\Fortify\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Tests\Models\UserWithTwoFactor;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use Orchestra\Testbench\Attributes\WithConfig;
use Orchestra\Testbench\Attributes\WithMigration;
use PragmaRX\Google2FA\Google2FA;

#[WithMigration]
#[DefineEnvironment('withTwoFactorAuthentication')]
#[WithConfig('auth.providers.users.model', UserWithTwoFactor::class)]
class VerifyTwoFactorCodeControllerTest extends OrchestraTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Model::encryptUsing(null);
    }

    public function test_two_factor_code_can_be_verified()
    {
        $tfaEngine = app(Google2FA::class);
        $userSecret = $tfaEngine->generateSecretKey();
        $validOtp = $tfaEngine->getCurrentOtp($userSecret);

        $user = UserWithTwoFactor::forceCreate([
            'name' => 'Paulinus Perekpo',
            'email' => 'perepaul@laravel.com',
            'password' => bcrypt('secret'),
            'two_factor_secret' => encrypt($userSecret),
        ]);

        $response = $this->withoutExceptionHandling()->actingAs($user)->postJson(
            '/user/verify-two-factor-code', ['code' => $validOtp]
        );

        $response->assertStatus(200);
    }

    public function test_two_factor_code_verification_fails_with_invalid_code()
    {
        $tfaEngine = app(Google2FA::class);
        $userSecret = $tfaEngine->generateSecretKey();

        $user = UserWithTwoFactor::forceCreate([
            'name' => 'Paulinus Perekpo',
            'email' => 'perepaul@laravel.com',
            'password' => bcrypt('secret'),
            'two_factor_secret' => encrypt($userSecret),
        ]);

        $response = $this->withExceptionHandling()->actingAs($user)->postJson(
            '/user/verify-two-factor-code', ['code' => 'invalid-code']
        );

        $response->assertStatus(422);
    }

    public function test_two_factor_code_verification_fails_without_code()
    {
        $user = UserWithTwoFactor::forceCreate([
            'name' => 'Paulinus Perekpo',
            'email' => 'perepaul@laravel.com',
            'password' => bcrypt('secret'),
            'two_factor_secret' => encrypt('test-secret'),
        ]);

        $response = $this->withExceptionHandling()->actingAs($user)->postJson(
            '/user/verify-two-factor-code', []
        );

        $response->assertStatus(422);
    }

    public function test_two_factor_code_verification_fails_without_two_factor_secret()
    {
        $user = UserWithTwoFactor::forceCreate([
            'name' => 'Paulinus Perekpo',
            'email' => 'perepaul@laravel.com',
            'password' => bcrypt('secret'),
        ]);

        $response = $this->withExceptionHandling()->actingAs($user)->postJson(
            '/user/verify-two-factor-code', ['code' => '123456']
        );

        $response->assertStatus(422);
    }

    public function test_two_factor_code_verification_requires_authentication()
    {
        $response = $this->withExceptionHandling()->postJson(
            '/user/verify-two-factor-code', ['code' => '123456']
        );

        $response->assertStatus(401);
    }

    #[DefineEnvironment('withConfirmedTwoFactorAuthentication')]
    public function test_two_factor_code_can_be_verified_when_confirmation_is_enabled()
    {
        $tfaEngine = app(Google2FA::class);
        $userSecret = $tfaEngine->generateSecretKey();
        $validOtp = $tfaEngine->getCurrentOtp($userSecret);

        $user = UserWithTwoFactor::forceCreate([
            'name' => 'Paulinus Perekpo',
            'email' => 'perepaul@laravel.com',
            'password' => bcrypt('secret'),
            'two_factor_secret' => encrypt($userSecret),
            'two_factor_confirmed_at' => now(),
        ]);

        $response = $this->withoutExceptionHandling()->actingAs($user)->postJson(
            '/user/verify-two-factor-code', ['code' => $validOtp]
        );

        $response->assertStatus(200);
    }

    #[DefineEnvironment('withConfirmedTwoFactorAuthentication')]
    public function test_two_factor_code_verification_fails_when_not_confirmed()
    {
        $tfaEngine = app(Google2FA::class);
        $userSecret = $tfaEngine->generateSecretKey();
        $validOtp = $tfaEngine->getCurrentOtp($userSecret);

        $user = UserWithTwoFactor::forceCreate([
            'name' => 'Paulinus Perekpo',
            'email' => 'perepaul@laravel.com',
            'password' => bcrypt('secret'),
            'two_factor_secret' => encrypt($userSecret),
            'two_factor_confirmed_at' => null,
        ]);

        $response = $this->withExceptionHandling()->actingAs($user)->postJson(
            '/user/verify-two-factor-code', ['code' => $validOtp]
        );

        $response->assertStatus(422);
    }

    public function test_two_factor_code_verification_sets_session_timestamp()
    {
        $tfaEngine = app(Google2FA::class);
        $userSecret = $tfaEngine->generateSecretKey();
        $validOtp = $tfaEngine->getCurrentOtp($userSecret);

        $user = UserWithTwoFactor::forceCreate([
            'name' => 'Paulinus Perekpo',
            'email' => 'perepaul@laravel.com',
            'password' => bcrypt('secret'),
            'two_factor_secret' => encrypt($userSecret),
        ]);

        $response = $this->withoutExceptionHandling()->actingAs($user)->postJson(
            '/user/verify-two-factor-code', ['code' => $validOtp]
        );

        $response->assertStatus(200)
            ->assertSessionHas('auth.two_factor_confirmed_at');
    }

    public function test_two_factor_code_verification_status_returns_confirmed()
    {
        $user = UserWithTwoFactor::forceCreate([
            'name' => 'Paulinus Perekpo',
            'email' => 'perepaul@laravel.com',
            'password' => bcrypt('secret'),
        ]);

        $response = $this->withoutExceptionHandling()
            ->actingAs($user)
            ->withSession(['auth.two_factor_confirmed_at' => time()])
            ->getJson('/user/verified-two-factor-code-status');

        $response->assertStatus(200)
            ->assertJson(['confirmed' => true]);
    }

    public function test_two_factor_code_verification_status_returns_not_confirmed()
    {
        $user = UserWithTwoFactor::forceCreate([
            'name' => 'Paulinus Perekpo',
            'email' => 'perepaul@laravel.com',
            'password' => bcrypt('secret'),
        ]);

        $response = $this->withoutExceptionHandling()
            ->actingAs($user)
            ->getJson('/user/verified-two-factor-code-status');

        $response->assertStatus(200)
            ->assertJson(['confirmed' => false]);
    }
}
