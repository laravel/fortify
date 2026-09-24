<?php

namespace Laravel\Fortify\Tests;

use App\Actions\Fortify\ResetUserPassword;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Password;
use JMac\Testing\Double;
use JMac\Testing\Matching\Argument;
use Laravel\Fortify\Contracts\ResetPasswordViewResponse;
use Laravel\Fortify\Contracts\ResetsUserPasswords;
use Laravel\Fortify\Fortify;
use Orchestra\Testbench\Attributes\WithMigration;

#[WithMigration]
class NewPasswordControllerTest extends OrchestraTestCase
{
    use RefreshDatabase;

    public function test_the_new_password_view_is_returned()
    {
        $this->double(ResetPasswordViewResponse::class)
            ->allows('toResponse')
            ->returns(response('hello world'));

        $response = $this->get('/reset-password/token');

        $response->assertStatus(200);
        $response->assertSeeText('hello world');
    }

    public function test_password_can_be_reset()
    {
        Event::fake([PasswordReset::class]);

        $user = UserFactory::new()->create(['email' => 'taylor@laravel.com']);
        $token = Password::broker()->createToken($user);

        $this->double(ResetsUserPasswords::class, ResetUserPassword::class)
            ->expects('reset')
            ->with(Argument::satisfies(fn ($resetUser) => $resetUser->is($user)), Argument::type('array'));

        $response = $this->withoutExceptionHandling()->post('/reset-password', [
            'token' => $token,
            'email' => 'taylor@laravel.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(Fortify::redirects('password-reset', route('login')));
        $this->assertGuest();
        $this->assertNotSame($user->remember_token, $user->fresh()->remember_token);
        Event::assertDispatched(PasswordReset::class);
    }

    public function test_password_reset_can_fail()
    {
        UserFactory::new()->create(['email' => 'taylor@laravel.com']);

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
        UserFactory::new()->create(['email' => 'taylor@laravel.com']);

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

        $guard = $this->double(StatefulGuard::class);
        $user = Double::for(User::class);

        $user->expects('setRememberToken');
        $user->expects('save');

        $guard->expects('login')->never();

        $updater = $this->double(ResetsUserPasswords::class, ResetUserPassword::class);
        $updater->expects('reset')->with($user, Argument::type('array'));

        $broker->expects('reset')->resolves(function ($input, $callback) use ($user) {
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

        $user = UserFactory::new()->create(['email' => 'john.doe@example.com']);
        $token = Password::broker()->createToken($user);

        $this->double(ResetsUserPasswords::class, ResetUserPassword::class)
            ->expects('reset')
            ->with(Argument::satisfies(fn ($resetUser) => $resetUser->is($user)), Argument::type('array'));

        $response = $this->withoutExceptionHandling()->post('/reset-password', [
            'token' => $token,
            'email' => 'John.Doe@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(Fortify::redirects('password-reset', route('login')));
    }
}
