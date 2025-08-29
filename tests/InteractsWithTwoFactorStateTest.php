<?php

namespace Laravel\Fortify\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Laravel\Fortify\Tests\Models\UserWithTwoFactor;
use Laravel\Fortify\Tests\Requests\FormRequestInteractsWithTwoFactorState;
use Orchestra\Testbench\Attributes\WithMigration;
use PHPUnit\Framework\Attributes\DataProvider;

#[WithMigration]
class InteractsWithTwoFactorStateTest extends OrchestraTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('fortify.features', [Features::twoFactorAuthentication()]);
        $this->app['config']->set('fortify-options.two-factor-authentication.confirm', true);
    }

    public function test_validation_is_skipped_when_confirm_feature_is_disabled()
    {
        $this->app['config']->set('fortify-options.two-factor-authentication.confirm', false);

        $formRequest = $this->createFormRequestWithUser();

        $formRequest->ensureStateIsValid();

        $this->assertFalse($formRequest->session()->has('two_factor_empty_at'));
        $this->assertFalse($formRequest->session()->has('two_factor_confirming_at'));
    }

    #[DataProvider('twoFactorStatesProvider')]
    public function test_sets_empty_at_session_when_two_factor_is_disabled(?string $secret, ?string $confirmedAt, bool $expectedDisabled)
    {
        $attributes = [
            'two_factor_secret' => $secret ? encrypt($secret) : null,
            'two_factor_confirmed_at' => $confirmedAt === 'confirmed' ? now() : $confirmedAt,
        ];
        $user = $this->createUser($attributes);
        $formRequest = $this->createFormRequestWithUser($user);

        $formRequest->ensureStateIsValid();

        if (! $expectedDisabled) {
            $this->assertFalse($formRequest->session()->has('two_factor_empty_at'));

            return;
        }

        $this->assertTrue($formRequest->session()->has('two_factor_empty_at'));
        $this->assertIsInt($formRequest->session()->get('two_factor_empty_at'));
    }

    public static function twoFactorStatesProvider(): array
    {
        return [
            'disabled' => [null, null, true],
            'partial_setup' => ['secret', null, true],
            'enabled' => ['secret', 'confirmed', false],
        ];
    }

    public function test_sets_empty_at_when_two_factor_not_fully_enabled()
    {
        $user = $this->createUser([
            'two_factor_secret' => encrypt('secret'),
            'two_factor_confirmed_at' => null,
        ]);
        $formRequest = $this->createFormRequestWithUser($user);

        $formRequest->ensureStateIsValid();

        $this->assertTrue($formRequest->session()->has('two_factor_empty_at'));
        $this->assertIsInt($formRequest->session()->get('two_factor_empty_at'));
    }

    public function test_sets_confirming_at_when_user_begins_confirmation_process()
    {
        $user = $this->createUser([
            'two_factor_secret' => encrypt('secret'),
            'two_factor_confirmed_at' => null,
        ]);
        $formRequest = $this->createFormRequestWithUser($user);
        $formRequest->session()->put('two_factor_empty_at', time() - 10);

        $formRequest->ensureStateIsValid();

        $this->assertTrue($formRequest->session()->has('two_factor_confirming_at'));
        $this->assertIsInt($formRequest->session()->get('two_factor_confirming_at'));
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
        $formRequest = $this->createFormRequestWithUser($user);

        foreach ($sessionData as $key => $value) {
            $formRequest->session()->put($key, $value);
        }

        $formRequest->ensureStateIsValid();

        $this->assertFalse($formRequest->session()->has('two_factor_confirming_at'), $description);
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
        $formRequest = $this->createFormRequestWithUser($user);
        $formRequest->session()->put('two_factor_confirming_at', time() - 10);

        $formRequest->ensureStateIsValid();

        $this->assertNull($user->two_factor_secret);
        $this->assertTrue($formRequest->session()->has('two_factor_empty_at'));
        $this->assertFalse($formRequest->session()->has('two_factor_confirming_at'));
    }

    public function test_disabled_to_confirming_to_abandoned_state()
    {
        $user = $this->createUser([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ]);
        $formRequest = $this->createFormRequestWithUser($user);

        $formRequest->ensureStateIsValid();
        $this->assertTrue($formRequest->session()->has('two_factor_empty_at'));

        $user->two_factor_secret = encrypt('secret');
        $user->save();
        $formRequest = $this->createFormRequestWithUser($user);
        $formRequest->ensureStateIsValid();
        $this->assertTrue($formRequest->session()->has('two_factor_confirming_at'));

        $formRequest->session()->put('two_factor_confirming_at', time() - 10);
        $formRequest = $this->createFormRequestWithUser($user);
        $formRequest->ensureStateIsValid();

        $this->assertNull($user->two_factor_secret);
        $this->assertTrue($formRequest->session()->has('two_factor_empty_at'));
        $this->assertFalse($formRequest->session()->has('two_factor_confirming_at'));
    }

    public function test_does_not_disable_when_code_in_old_input_is_present()
    {
        $user = $this->createUser([
            'two_factor_secret' => encrypt('secret'),
            'two_factor_confirmed_at' => null,
        ]);
        $formRequest = $this->createFormRequestWithUser($user);
        $formRequest->session()->put('two_factor_confirming_at', time() - 10);
        $formRequest->session()->flashInput(['code' => '123456']);

        $formRequest->ensureStateIsValid();

        $this->assertNotNull($user->two_factor_secret);
        $this->assertTrue($formRequest->session()->has('two_factor_empty_at'));
        $this->assertTrue($formRequest->session()->has('two_factor_confirming_at'));
    }

    public function test_confirming_at_timestamp_is_current_time()
    {
        $user = $this->createUser([
            'two_factor_secret' => encrypt('secret'),
            'two_factor_confirmed_at' => null,
        ]);
        $formRequest = $this->createFormRequestWithUser($user);
        $formRequest->session()->put('two_factor_empty_at', time() - 10);

        $beforeTime = time();
        $formRequest->ensureStateIsValid();
        $afterTime = time();

        $timestamp = $formRequest->session()->get('two_factor_confirming_at');
        $this->assertGreaterThanOrEqual($beforeTime, $timestamp);
        $this->assertLessThanOrEqual($afterTime, $timestamp);
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

    private function createFormRequestWithUser(?UserWithTwoFactor $user = null): FormRequestInteractsWithTwoFactorState
    {
        $formRequest = FormRequestInteractsWithTwoFactorState::create('test');
        $formRequest->setUserResolver(fn () => $user);
        $formRequest->setLaravelSession($this->app['session']->driver());

        return $formRequest;
    }
}
