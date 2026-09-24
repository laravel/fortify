<?php

namespace Laravel\Fortify\Tests;

use Database\Factories\UserFactory;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Passwords\PasswordBrokerManager;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use JMac\Testing\Double;
use Laravel\Fortify\Contracts\RequestPasswordResetLinkViewResponse;
use Orchestra\Testbench\Attributes\WithMigration;

#[WithMigration]
class PasswordResetLinkRequestControllerTest extends OrchestraTestCase
{
    use RefreshDatabase;

    public function test_the_reset_link_request_view_is_returned()
    {
        $this->double(RequestPasswordResetLinkViewResponse::class)
            ->allows('toResponse')
            ->returns(response('hello world'));

        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
        $response->assertSeeText('hello world');
    }

    public function test_reset_link_can_be_successfully_requested()
    {
        Notification::fake();

        $user = UserFactory::new()->create(['email' => 'taylor@laravel.com']);

        $response = $this->from(url('/forgot-password'))
            ->post('/forgot-password', ['email' => 'taylor@laravel.com']);

        $response->assertStatus(302);
        $response->assertRedirect('/forgot-password');
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('status', trans(Password::RESET_LINK_SENT));
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_link_request_can_fail()
    {
        Notification::fake();

        $response = $this->from(url('/forgot-password'))
            ->post('/forgot-password', ['email' => 'taylor@laravel.com']);

        $response->assertStatus(302);
        $response->assertRedirect('/forgot-password');
        $response->assertSessionHasErrors('email');
        Notification::assertNothingSent();
    }

    public function test_reset_link_request_can_fail_with_json()
    {
        Notification::fake();

        $response = $this->from(url('/forgot-password'))
            ->postJson('/forgot-password', ['email' => 'taylor@laravel.com']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
        Notification::assertNothingSent();
    }

    public function test_reset_link_can_be_successfully_requested_with_customized_email_field()
    {
        Config::set('fortify.email', 'emailAddress');
        $manager = Double::for(PasswordBrokerManager::class);
        $manager->allows('broker')->returns($broker = Double::for(PasswordBroker::class));
        Password::swap($manager);

        $broker->expects('sendResetLink')->returns(Password::RESET_LINK_SENT);

        $response = $this->from(url('/forgot-password'))
            ->post('/forgot-password', ['emailAddress' => 'taylor@laravel.com']);

        $response->assertStatus(302);
        $response->assertRedirect('/forgot-password');
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('status', trans(Password::RESET_LINK_SENT));
    }

    public function test_case_insensitive_usernames_can_be_used()
    {
        Config::set('fortify.lowercase_usernames', true);
        Notification::fake();

        $user = UserFactory::new()->create(['email' => 'taylor@laravel.com']);

        $response = $this->from(url('/forgot-password'))
            ->post('/forgot-password', ['email' => 'TAYLOR@laravel.com']);

        $response->assertStatus(302);
        $response->assertRedirect('/forgot-password');
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('status', trans(Password::RESET_LINK_SENT));
        Notification::assertSentTo($user, ResetPassword::class);
    }
}
