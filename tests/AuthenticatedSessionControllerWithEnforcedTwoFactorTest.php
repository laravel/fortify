<?php

namespace Laravel\Fortify\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Fortify\Events\TwoFactorAuthenticationChallenged;
use Laravel\Fortify\Events\TwoFactorAuthenticationSetupRequired;
use Laravel\Fortify\Tests\Models\UserWithTwoFactor;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use Orchestra\Testbench\Attributes\WithConfig;
use Orchestra\Testbench\Attributes\WithMigration;
use PragmaRX\Google2FA\Google2FA;

#[WithMigration]
#[DefineEnvironment('withTwoFactorAuthentication')]
#[WithConfig('auth.providers.users.model', UserWithTwoFactor::class)]
#[WithConfig('fortify.enforce_two_factor_auth', true)]
class AuthenticatedSessionControllerWithEnforcedTwoFactorTest extends OrchestraTestCase
{
    use RefreshDatabase;

    public function test_user_is_redirected_to_setup_page_when_two_factor_is_enforced()
    {
        Event::fake();

        UserWithTwoFactor::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => bcrypt('secret'),
        ]);

        $response = $this->withoutExceptionHandling()->post('/login', [
            'email' => 'taylor@laravel.com',
            'password' => 'secret',
        ]);

        $response->assertRedirect('/two-factor-setup');

        Event::assertDispatched(TwoFactorAuthenticationSetupRequired::class);
    }

    public function test_json_response_contains_data_required_for_setting_up_two_factor_when_enforced()
    {
        Event::fake();

        $user = UserWithTwoFactor::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => bcrypt('secret'),
        ]);

        $response = $this->withoutExceptionHandling()->postJson('/login', [
            'email' => 'taylor@laravel.com',
            'password' => 'secret',
        ]);

        $user->refresh();

        $response->assertJson([
            'two_factor_setup_required' => true,
            'setup_info' => [
                'code' => decrypt($user->two_factor_secret),
                'url' => $user->twoFactorQrCodeUrl(),
                'qr_svg' => $user->twoFactorQrCodeSvg(),
                'recovery_codes' => $user->recoveryCodes(),
            ],
        ]);
    }

    public function test_user_is_redirected_to_challenge_when_two_factor_is_already_setup()
    {
        Event::fake();

        UserWithTwoFactor::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => bcrypt('secret'),
            'two_factor_secret' => 'test-secret',
        ]);

        $response = $this->withoutExceptionHandling()->post('/login', [
            'email' => 'taylor@laravel.com',
            'password' => 'secret',
        ]);

        Event::assertDispatched(TwoFactorAuthenticationChallenged::class);

        $response->assertRedirect('/two-factor-challenge');
    }

    #[DefineEnvironment('withConfirmedTwoFactorAuthentication')]
    public function test_two_factor_is_confirmed_when_feature_enabled_after_successful_setup()
    {
        Event::fake();

        $user = UserWithTwoFactor::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => bcrypt('secret'),
        ]);

        $this->withoutExceptionHandling()->post('/login', [
            'email' => 'taylor@laravel.com',
            'password' => 'secret',
        ]);

        // Now get a valid key
        $tfaEngine = app(Google2FA::class);
        $validOtp = $tfaEngine->getCurrentOtp(
            decrypt($user->refresh()->two_factor_secret)
        );

        $this->post('/two-factor-setup', [
            'code' => $validOtp,
        ]);

        $user->refresh();

        $this->assertNotNull($user->two_factor_confirmed_at);
    }

    #[DefineEnvironment('withConfirmedTwoFactorAuthentication')]
    public function test_user_is_redirected_to_home_when_two_factor_is_successfully_set_up()
    {
        Event::fake();

        $user = UserWithTwoFactor::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => bcrypt('secret'),
        ]);

        $this->withoutExceptionHandling()->post('/login', [
            'email' => 'taylor@laravel.com',
            'password' => 'secret',
        ]);

        // Now get a valid key
        $tfaEngine = app(Google2FA::class);
        $validOtp = $tfaEngine->getCurrentOtp(
            decrypt($user->refresh()->two_factor_secret)
        );

        $response = $this->post('/two-factor-setup', [
            'code' => $validOtp,
        ]);

        $response->assertRedirect('/home')
            ->assertSessionMissing('login.id');
    }

    #[DefineEnvironment('withConfirmedTwoFactorAuthentication')]
    public function test_setup_fails_if_confirm_two_factor_is_enabled_and_code_is_incorrect()
    {
        Event::fake();

        UserWithTwoFactor::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => bcrypt('secret'),
        ]);

        $this->post('/login', [
            'email' => 'taylor@laravel.com',
            'password' => 'secret',
        ]);

        $response = $this->post('/two-factor-setup', [
            'code' => '123',
        ]);

        $response->assertRedirect('/two-factor-setup')
            ->assertSessionHas('login.id')
            ->assertSessionHasErrors(['code']);
    }
}
