<?php

namespace Laravel\Fortify\Tests;

use Illuminate\Foundation\Auth\User;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Password;
use JMac\Testing\Double;
use JMac\Testing\Matching\Argument;
use Laravel\Fortify\Contracts\ResetPasswordViewResponse;
use Laravel\Fortify\Contracts\ResetsUserPasswords;
use Laravel\Fortify\Fortify;

class NewPasswordControllerTest extends OrchestraTestCase
{
    public function test_the_new_password_view_is_returned()
    {
        $this->mock(ResetPasswordViewResponse::class)
            ->shouldReceive('toResponse')
            ->andReturn(response('hello world'));

        $response = $this->get('/reset-password/token');

        $response->assertStatus(200);
        $response->assertSeeText('hello world');
    }

    public function test_password_can_be_reset()
    {
        Password::shouldReceive('broker')->andReturn($broker = Double::for(PasswordBroker::class));

        $guard = $this->mock(StatefulGuard::class);
        $user = Double::for(User::class);

        $user->expects('setRememberToken');
        $user->expects('save');

        $guard->expects('login')->never();

        $updater = $this->mock(ResetsUserPasswords::class);
        $updater->expects('reset')->with($user, Argument::type('array'));

        $broker->allows('reset')->resolves(function ($input, $callback) use ($user) {
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
        Password::shouldReceive('broker')->andReturn($broker = Double::for(PasswordBroker::class));

        $broker->allows('reset')->resolves(function ($input, $callback) {
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
        Password::shouldReceive('broker')->andReturn($broker = Double::for(PasswordBroker::class));

        $broker->allows('reset')->resolves(function ($input, $callback) {
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
        Password::shouldReceive('broker')->andReturn($broker = Double::for(PasswordBroker::class));

        $guard = $this->mock(StatefulGuard::class);
        $user = Double::for(User::class);

        $user->expects('setRememberToken');
        $user->expects('save');

        $guard->expects('login')->never();

        $updater = $this->mock(ResetsUserPasswords::class);
        $updater->expects('reset')->with($user, Argument::type('array'));

        $broker->allows('reset')->resolves(function ($input, $callback) use ($user) {
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

    public function test_case_insensitive_usernames_can_be_used()
    {
        Config::set('fortify.lowercase_usernames', true);
        Password::shouldReceive('broker')->andReturn($broker = Double::for(PasswordBroker::class));

        $guard = $this->mock(StatefulGuard::class);
        $user = Double::for(User::class);

        $user->expects('setRememberToken');
        $user->expects('save');
        $guard->expects('login')->never();

        $updater = $this->mock(ResetsUserPasswords::class);
        $updater->expects('reset')->with($user, Argument::type('array'));

        $broker->expects('reset')->with(Argument::satisfies(fn ($credentials) => $credentials['email'] === 'john.doe@example.com'),
            Argument::type('callable'))->resolves(function ($input, $callback) use ($user) {
                $callback($user, 'password');

                return Password::PASSWORD_RESET;
            });

        $response = $this->withoutExceptionHandling()->post('/reset-password', [
            'token' => 'token',
            'email' => 'John.Doe@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(Fortify::redirects('password-reset', route('login')));
    }
}
