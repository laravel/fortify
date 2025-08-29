<?php

namespace Laravel\Fortify\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Features;
use Laravel\Fortify\Tests\Controllers\ControllerWithManagesTwoFactorConfirmation;
use Laravel\Fortify\Tests\Models\UserWithTwoFactor;
use Orchestra\Testbench\Attributes\WithMigration;
use PHPUnit\Framework\Attributes\DataProvider;

#[WithMigration]
class ManagesTwoFactorConfirmationTest extends OrchestraTestCase
{
    use RefreshDatabase;

    private ControllerWithManagesTwoFactorConfirmation $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('fortify.features', [Features::twoFactorAuthentication()]);
        $this->app['config']->set('fortify-options.two-factor-authentication.confirm', true);

        $this->controller = new ControllerWithManagesTwoFactorConfirmation();
    }

    public function test_validation_is_skipped_when_confirm_feature_is_disabled()
    {
        $this->app['config']->set('fortify-options.two-factor-authentication.confirm', false);

        $request = $this->createRequestWithUser();

        $this->controller->callValidateTwoFactorAuthenticationState($request);

        $this->assertFalse($request->session()->has('two_factor_empty_at'));
        $this->assertFalse($request->session()->has('two_factor_confirming_at'));
    }

    #[DataProvider('twoFactorStatesProvider')]
    public function test_sets_empty_at_session_when_two_factor_is_disabled(?string $secret, ?string $confirmedAt, bool $expectedDisabled)
    {
        $attributes = [
            'two_factor_secret' => $secret ? encrypt($secret) : null,
            'two_factor_confirmed_at' => $confirmedAt === 'confirmed' ? now() : $confirmedAt,
        ];
        $user = $this->createUser($attributes);
        $request = $this->createRequestWithUser($user);

        $this->controller->callValidateTwoFactorAuthenticationState($request);

        if (! $expectedDisabled) {
            $this->assertFalse($request->session()->has('two_factor_empty_at'));

            return;
        }

        $this->assertTrue($request->session()->has('two_factor_empty_at'));
        $this->assertIsInt($request->session()->get('two_factor_empty_at'));
    }

    public static function twoFactorStatesProvider(): array
    {
        return [
            'disabled' => [null, null, true],
            'enabled' => ['secret', 'confirmed', false],
        ];
    }

    public function test_sets_confirming_at_when_user_begins_confirmation_process()
    {
        $user = $this->createUser([
            'two_factor_secret' => encrypt('secret'),
            'two_factor_confirmed_at' => null,
        ]);
        $request = $this->createRequestWithUser($user);
        $request->session()->put('two_factor_empty_at', time() - 10);

        $this->controller->callValidateTwoFactorAuthenticationState($request);

        $this->assertTrue($request->session()->has('two_factor_confirming_at'));
        $this->assertIsInt($request->session()->get('two_factor_confirming_at'));
    }

    #[DataProvider('confirmationBlockersProvider')]
    public function test_does_not_set_confirming_at_when_conditions_not_met(array $userAttributes, array $sessionData, string $description)
    {
        $attributes = $userAttributes;
        if ($attributes['two_factor_secret'] === 'secret') {
            $attributes['two_factor_secret'] = encrypt('secret');
        }
        if ($attributes['two_factor_confirmed_at'] === 'confirmed') {
            $attributes['two_factor_confirmed_at'] = now();
        }
        $user = $this->createUser($attributes);
        $request = $this->createRequestWithUser($user);

        foreach ($sessionData as $key => $value) {
            $request->session()->put($key, $value);
        }

        $this->controller->callValidateTwoFactorAuthenticationState($request);

        $this->assertFalse($request->session()->has('two_factor_confirming_at'), $description);
    }

    public static function confirmationBlockersProvider(): array
    {
        $pastTime = time() - 10;
        return [
            'no_secret' => [
                ['two_factor_secret' => null, 'two_factor_confirmed_at' => null],
                ['two_factor_empty_at' => $pastTime],
                'Should not set confirming_at without secret',
            ],
            'already_confirmed' => [
                ['two_factor_secret' => 'secret', 'two_factor_confirmed_at' => 'confirmed'],
                ['two_factor_empty_at' => $pastTime],
                'Should not set confirming_at when already confirmed',
            ],
            'no_empty_at_session' => [
                ['two_factor_secret' => 'secret', 'two_factor_confirmed_at' => null],
                [],
                'Should not set confirming_at without empty_at session',
            ],
            'already_confirming' => [
                ['two_factor_secret' => 'secret', 'two_factor_confirmed_at' => null],
                ['two_factor_empty_at' => $pastTime, 'two_factor_confirming_at' => time() - 5],
                'Should not overwrite existing confirming_at timestamp',
            ],
        ];
    }

    public function test_disables_two_factor_when_confirmation_is_abandoned()
    {
        $user = $this->createUser([
            'two_factor_secret' => encrypt('secret'),
            'two_factor_confirmed_at' => null,
        ]);
        $request = $this->createRequestWithUser($user);
        $request->session()->put('two_factor_confirming_at', time() - 10);

        $this->controller->callValidateTwoFactorAuthenticationState($request);

        $this->assertNull($user->two_factor_secret);
        $this->assertTrue($request->session()->has('two_factor_empty_at'));
        $this->assertFalse($request->session()->has('two_factor_confirming_at'));
    }

    public function test_disabled_to_confirming_to_abandoned_state()
    {
        $user = $this->createUser([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ]);
        $request = $this->createRequestWithUser($user);

        $this->controller->callValidateTwoFactorAuthenticationState($request);
        $this->assertTrue($request->session()->has('two_factor_empty_at'));

        $user->two_factor_secret = encrypt('secret');
        $user->save();
        $this->controller->callValidateTwoFactorAuthenticationState($request);
        $this->assertTrue($request->session()->has('two_factor_confirming_at'));

        $request->session()->put('two_factor_confirming_at', time() - 10);
        $this->controller->callValidateTwoFactorAuthenticationState($request);

        $this->assertNull($user->two_factor_secret);
        $this->assertTrue($request->session()->has('two_factor_empty_at'));
        $this->assertFalse($request->session()->has('two_factor_confirming_at'));
    }

    private function createUser(array $attributes = []): UserWithTwoFactor
    {
        $defaults = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ];

        return UserWithTwoFactor::forceCreate(array_merge($defaults, $attributes));
    }

    private function createRequestWithUser(?UserWithTwoFactor $user = null): Request
    {
        $user = $user ?: $this->createUser();

        Auth::shouldReceive('user')->andReturn($user);

        $request = Request::create('test');
        $request->setUserResolver(fn () => $user);
        $request->setLaravelSession($this->app['session']->driver());

        return $request;
    }
}
