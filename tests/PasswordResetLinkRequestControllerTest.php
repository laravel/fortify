<?php

namespace Laravel\Fortify\Tests;

use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Password;
use Laravel\Fortify\Contracts\RequestPasswordResetLinkViewResponse;
use Mockery;

class PasswordResetLinkRequestControllerTest extends OrchestraTestCase
{
    public function test_the_reset_link_request_view_is_returned()
    {
        $this->mock(RequestPasswordResetLinkViewResponse::class)
                ->shouldReceive('toResponse')
                ->andReturn(response('hello world'));

        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
        $response->assertSeeText('hello world');
    }

    public function test_reset_link_can_be_successfully_requested()
    {
        Password::shouldReceive('broker')->andReturn($broker = Mockery::mock(PasswordBroker::class));

        $broker->shouldReceive('sendResetLink')->andReturn(Password::RESET_LINK_SENT);

        $response = $this->from(url('/forgot-password'))
                        ->post('/forgot-password', ['email' => 'taylor@laravel.com']);

        $response->assertStatus(302);
        $response->assertRedirect('/forgot-password');
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('status', trans(Password::RESET_LINK_SENT));
    }

    public function test_reset_link_request_can_fail()
    {
        Password::shouldReceive('broker')->andReturn($broker = Mockery::mock(PasswordBroker::class));

        $broker->shouldReceive('sendResetLink')->andReturn(Password::INVALID_USER);

        $response = $this->from(url('/forgot-password'))
                        ->post('/forgot-password', ['email' => 'taylor@laravel.com']);

        $response->assertStatus(302);
        $response->assertRedirect('/forgot-password');
        $response->assertSessionHasErrors('email');
    }

    public function test_reset_link_request_can_fail_with_json()
    {
        Password::shouldReceive('broker')->andReturn($broker = Mockery::mock(PasswordBroker::class));

        $broker->shouldReceive('sendResetLink')->andReturn(Password::INVALID_USER);

        $response = $this->from(url('/forgot-password'))
                        ->postJson('/forgot-password', ['email' => 'taylor@laravel.com']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    public function test_reset_link_can_be_successfully_requested_with_customized_email_field()
    {
        Config::set('fortify.email', 'emailAddress');
        Password::shouldReceive('broker')->andReturn($broker = Mockery::mock(PasswordBroker::class));

        $broker->shouldReceive('sendResetLink')->andReturn(Password::RESET_LINK_SENT);

        $response = $this->from(url('/forgot-password'))
            ->post('/forgot-password', ['emailAddress' => 'taylor@laravel.com']);

        $response->assertStatus(302);
        $response->assertRedirect('/forgot-password');
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('status', trans(Password::RESET_LINK_SENT));
    }
}
