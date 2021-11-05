<?php

namespace Laravel\Fortify\Tests;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Password;
use Laravel\Fortify\Contracts\ResetPasswordViewResponse;
use Laravel\Fortify\Contracts\ResetsUserPasswords;
use Laravel\Fortify\Fortify;
use Mockery;

class NewPasswordControllerTest extends OrchestraTestCase
{
    protected $user;

    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testbench']);

        $this->artisan('migrate', ['--database' => 'testbench'])->run();

        $this->user = TestNewPasswordUser::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => bcrypt('secret'),
        ]);

        $this->broker()->sendResetLink(['email' => $this->user->email], function($user, $token) {
            $this->token = $token;
        });
    }

    public function test_the_new_password_view_is_returned()
    {
        $this->mock(ResetPasswordViewResponse::class)
                ->shouldReceive('toResponse')
                ->andReturn(response('hello world'));

        $response = $this->get('/reset-password/' . $this->token . '?email=' . $this->user->email);

        $response->assertStatus(200);
        $response->assertSeeText('hello world');
    }

    public function test_password_can_be_reset()
    {
        Password::shouldReceive('broker')->andReturn($broker = Mockery::mock(PasswordBroker::class));

        $guard = $this->mock(StatefulGuard::class);
        $user = Mockery::mock(Authenticatable::class);

        $user->shouldReceive('setRememberToken')->once();
        $user->shouldReceive('save')->once();

        $guard->shouldReceive('login')->never();

        $updater = $this->mock(ResetsUserPasswords::class);
        $updater->shouldReceive('reset')->once()->with($user, Mockery::type('array'));

        $broker->shouldReceive('reset')->andReturnUsing(function ($input, $callback) use ($user) {
            $callback($user, 'password');

            return Password::PASSWORD_RESET;
        });

        $response = $this->withoutExceptionHandling()->post('/reset-password', [
            'token' => 'token',
            'email' => 'taylor@laravel.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(Fortify::redirects('password-reset', route('login')));
    }

    public function test_password_reset_can_fail()
    {
        Password::shouldReceive('broker')->andReturn($broker = Mockery::mock(PasswordBroker::class));

        $broker->shouldReceive('reset')->andReturnUsing(function ($input, $callback) {
            return Password::INVALID_TOKEN;
        });

        $response = $this->withoutExceptionHandling()->post('/reset-password', [
            'token' => 'token',
            'email' => 'taylor@laravel.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
    }

    public function test_password_reset_can_fail_with_json()
    {
        Password::shouldReceive('broker')->andReturn($broker = Mockery::mock(PasswordBroker::class));

        $broker->shouldReceive('reset')->andReturnUsing(function ($input, $callback) {
            return Password::INVALID_TOKEN;
        });

        $response = $this->postJson('/reset-password', [
            'token' => 'token',
            'email' => 'taylor@laravel.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    public function test_password_can_be_reset_with_customized_email_address_field()
    {
        Config::set('fortify.email', 'emailAddress');
        Password::shouldReceive('broker')->andReturn($broker = Mockery::mock(PasswordBroker::class));

        $guard = $this->mock(StatefulGuard::class);
        $user = Mockery::mock(Authenticatable::class);

        $user->shouldReceive('setRememberToken')->once();
        $user->shouldReceive('save')->once();

        $guard->shouldReceive('login')->never();

        $updater = $this->mock(ResetsUserPasswords::class);
        $updater->shouldReceive('reset')->once()->with($user, Mockery::type('array'));

        $broker->shouldReceive('reset')->andReturnUsing(function ($input, $callback) use ($user) {
            $callback($user, 'password');

            return Password::PASSWORD_RESET;
        });

        $response = $this->withoutExceptionHandling()->post('/reset-password', [
            'token' => 'token',
            'emailAddress' => 'taylor@laravel.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(Fortify::redirects('password-reset', route('login')));
    }

    public function test_password_is_required()
    {
        $response = $this->post('/reset-password', [
            'token' => 'token',
            'email' => 'taylor@laravel.com',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['password']);
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['migrator']->path(__DIR__.'/../database/migrations');

        $app['config']->set('auth.providers.users.model', TestNewPasswordUser::class);

        $app['config']->set('database.default', 'testbench');

        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    protected function broker(): PasswordBroker
    {
        return Password::broker(config('fortify.passwords'));
    }
}

class TestNewPasswordUser extends User
{
    protected $table = 'users';
}
