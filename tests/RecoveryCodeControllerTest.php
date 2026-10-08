<?php

namespace Laravel\Fortify\Tests;

use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Laravel\Fortify\Contracts\RecoveryCodesResponse;
use Laravel\Fortify\Events\RecoveryCodesGenerated;

class RecoveryCodeControllerTest extends OrchestraTestCase
{
    use RefreshDatabase;

    public function test_new_recovery_codes_can_be_generated()
    {
        Event::fake();

        $user = TestTwoFactorRecoveryCodeUser::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => bcrypt('secret'),
        ]);

        $response = $this->withoutExceptionHandling()->actingAs($user)->postJson(
            '/user/two-factor-recovery-codes'
        );

        $response->assertStatus(200);

        Event::assertDispatched(RecoveryCodesGenerated::class);

        $user->fresh();

        $this->assertNotNull($user->two_factor_recovery_codes);
        $this->assertIsArray(json_decode(decrypt($user->two_factor_recovery_codes), true));
    }

    public function test_recovery_codes_can_be_retrieved()
    {
        $user = TestTwoFactorRecoveryCodeUser::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => bcrypt('secret'),
            'two_factor_secret' => encrypt('foo'),
            'two_factor_recovery_codes' => encrypt(json_encode(['code-one', 'code-two'])),
        ]);

        $response = $this->withoutExceptionHandling()->actingAs($user)->getJson(
            '/user/two-factor-recovery-codes'
        );

        $response->assertStatus(200);
        $response->assertExactJson(['code-one', 'code-two']);
    }

    public function test_recovery_codes_are_empty_when_two_factor_is_not_enabled()
    {
        $user = TestTwoFactorRecoveryCodeUser::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => bcrypt('secret'),
        ]);

        $response = $this->withoutExceptionHandling()->actingAs($user)->getJson(
            '/user/two-factor-recovery-codes'
        );

        $response->assertStatus(200);
        $response->assertExactJson([]);
    }

    public function test_recovery_codes_response_can_be_customized()
    {
        $this->double(RecoveryCodesResponse::class)
            ->allows('toResponse')
            ->returns(new JsonResponse(['foo' => 'bar']));

        $user = TestTwoFactorRecoveryCodeUser::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => bcrypt('secret'),
            'two_factor_secret' => encrypt('foo'),
            'two_factor_recovery_codes' => encrypt(json_encode(['code-one', 'code-two'])),
        ]);

        $response = $this->withoutExceptionHandling()->actingAs($user)->getJson(
            '/user/two-factor-recovery-codes'
        );

        $response->assertStatus(200);
        $response->assertExactJson(['foo' => 'bar']);
    }
}

class TestTwoFactorRecoveryCodeUser extends User
{
    protected $table = 'users';
}
