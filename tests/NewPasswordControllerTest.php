<?php

namespace Laravel\Fortify\Tests;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Password;
use Laravel\Fortify\Contracts\ResetPasswordViewResponse;
use Laravel\Fortify\Contracts\ResetsUserPasswords;
use Laravel\Fortify\Fortify;
use Mockery;

class NewPasswordControllerTest extends OrchestraTestCase
{
    public function testTheNewPasswordViewIsReturned()
    {
        $this->mock(ResetPasswordViewResponse::class)
                ->shouldReceive('toResponse')
                ->andReturn(response('hello world'));

        $response = $this->get('/reset-password/token');

        $response->assertStatus(200);
        $response->assertSeeText('hello world');
    }

    public function testPasswordCanBeReset()
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

    public function testPasswordResetCanFail()
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

    public function testPasswordResetCanFailWithJson()
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

    public function testPasswordCanBeResetWithCustomizedEmailAddressField()
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

    public function testPasswordIsRequired()
    {
        $response = $this->post('/reset-password', [
            'token' => 'token',
            'email' => 'taylor@laravel.com',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['password']);
    }
}
