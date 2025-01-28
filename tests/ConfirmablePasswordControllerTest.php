<?php

namespace Laravel\Fortify\Tests;

use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Laravel\Fortify\Contracts\ConfirmPasswordViewResponse;
use Laravel\Fortify\Fortify;
use Orchestra\Testbench\Attributes\WithConfig;
use Orchestra\Testbench\Attributes\WithMigration;

#[WithMigration]
class ConfirmablePasswordControllerTest extends OrchestraTestCase
{
    use RefreshDatabase;

    protected $user;

    protected function afterRefreshingDatabase()
    {
        $this->user = TestConfirmPasswordUser::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => bcrypt('secret'),
        ]);
    }

    public function test_the_confirm_password_view_is_returned()
    {
        $this->mock(ConfirmPasswordViewResponse::class)
            ->shouldReceive('toResponse')
            ->andReturn(response('hello world'));

        $response = $this->withoutExceptionHandling()->actingAs($this->user)->get(
            '/user/confirm-password'
        );

        $response->assertStatus(200);
        $response->assertSeeText('hello world');
    }

    public function test_password_can_be_confirmed()
    {
        $this->freezeSecond();

        $response = $this->withoutExceptionHandling()
            ->actingAs($this->user)
            ->withSession(['url.intended' => 'http://foo.com/bar'])
            ->post(
                '/user/confirm-password',
                ['password' => 'secret']
            );

        $response->assertSessionHas('auth.password_confirmed_at', Date::now()->unix());
        $response->assertRedirect('http://foo.com/bar');
    }

    public function test_password_confirmation_can_fail_with_an_invalid_password()
    {
        $response = $this->withoutExceptionHandling()
            ->actingAs($this->user)
            ->withSession(['url.intended' => 'http://foo.com/bar'])
            ->post(
                '/user/confirm-password',
                ['password' => 'invalid']
            );

        $response->assertSessionHasErrors(['password']);
        $response->assertSessionMissing('auth.password_confirmed_at');
        $response->assertRedirect();
        $this->assertNotEquals($response->getTargetUrl(), 'http://foo.com/bar');
    }

    public function test_password_confirmation_can_fail_without_a_password()
    {
        $response = $this->withoutExceptionHandling()
            ->actingAs($this->user)
            ->withSession(['url.intended' => 'http://foo.com/bar'])
            ->post(
                '/user/confirm-password',
                ['password' => null]
            );

        $response->assertSessionHasErrors(['password']);
        $response->assertSessionMissing('auth.password_confirmed_at');
        $response->assertRedirect();
        $this->assertNotEquals($response->getTargetUrl(), 'http://foo.com/bar');
    }

    public function test_password_confirmation_can_be_customized()
    {
        $this->freezeSecond();

        Fortify::$confirmPasswordsUsingCallback = function () {
            return true;
        };

        $response = $this->withoutExceptionHandling()
            ->actingAs($this->user)
            ->withSession(['url.intended' => 'http://foo.com/bar'])
            ->post(
                '/user/confirm-password',
                ['password' => 'invalid']
            );

        $response->assertSessionHas('auth.password_confirmed_at', Date::now()->unix());
        $response->assertRedirect('http://foo.com/bar');

        Fortify::$confirmPasswordsUsingCallback = null;
    }

    public function test_password_confirmation_can_be_customized_and_fail_without_password()
    {
        $this->freezeSecond();

        Fortify::$confirmPasswordsUsingCallback = function () {
            return true;
        };

        $response = $this->withoutExceptionHandling()
            ->actingAs($this->user)
            ->withSession(['url.intended' => 'http://foo.com/bar'])
            ->post(
                '/user/confirm-password',
                ['password' => null]
            );

        $response->assertSessionHas('auth.password_confirmed_at', Date::now()->unix());
        $response->assertRedirect('http://foo.com/bar');

        Fortify::$confirmPasswordsUsingCallback = null;
    }

    public function test_password_can_be_confirmed_with_json()
    {
        $response = $this->actingAs($this->user)
            ->postJson(
                '/user/confirm-password',
                ['password' => 'secret']
            );

        $response->assertStatus(201);
    }

    public function test_password_confirmation_can_fail_with_json()
    {
        $response = $this->actingAs($this->user)
            ->postJson(
                '/user/confirm-password',
                ['password' => 'invalid']
            );

        $response->assertJsonValidationErrors('password');
    }

    #[WithConfig('auth.password_timeout', 120)]
    public function test_password_confirmation_status_has_been_confirmed()
    {
        Carbon::setTestNow();

        $response = $this->withoutExceptionHandling()
            ->actingAs($this->user)
            ->withSession(['auth.password_confirmed_at' => now()->subMinute(1)->unix()])
            ->get(
                '/user/confirmed-password-status',
            );

        $response->assertOk()
            ->assertJson(['confirmed' => true])
            ->assertHeader('X-Retry-After', 60);
    }

    #[WithConfig('auth.password_timeout', 120)]
    public function test_password_confirmation_status_has_expired()
    {
        Carbon::setTestNow();

        $response = $this->withoutExceptionHandling()
            ->actingAs($this->user)
            ->withSession(['auth.password_confirmed_at' => now()->subMinutes(10)->unix()])
            ->get(
                '/user/confirmed-password-status',
            );

        $response->assertOk()
            ->assertJson(['confirmed' => false])
            ->assertHeaderMissing('X-Retry-After');
    }

    #[WithConfig('auth.password_timeout', 120)]
    public function test_password_confirmation_status_has_not_confirmed()
    {
        Carbon::setTestNow();

        $response = $this->withoutExceptionHandling()
            ->actingAs($this->user)
            ->get(
                '/user/confirmed-password-status',
            );

        $response->assertOk()
            ->assertJson(['confirmed' => false])
            ->assertHeaderMissing('X-Retry-After');
    }

    protected function defineEnvironment($app)
    {
        parent::defineEnvironment($app);

        $app['config']->set([
            'auth.providers.users.model' => TestConfirmPasswordUser::class,
        ]);
    }
}

class TestConfirmPasswordUser extends User
{
    protected $table = 'users';
}
