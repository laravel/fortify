<?php

namespace Laravel\Fortify\Tests;

use Illuminate\Contracts\Auth\Authenticatable;
use Mockery;

class EmailVerificationNotificationControllerTest extends OrchestraTestCase
{
    public function test_email_verification_notification_can_be_sent()
    {
        $user = Mockery::mock(Authenticatable::class);

        $user->shouldReceive('hasVerifiedEmail')->andReturn(false);
        $user->shouldReceive('getAuthIdentifier')->andReturn(1);
        $user->shouldReceive('sendEmailVerificationNotification')->once();

        $response = $this->from('/email/verify')
                        ->actingAs($user)
                        ->post('/email/verification-notification');

        $response->assertRedirect('/email/verify');
    }

    public function test_user_is_redirect_if_already_verified()
    {
        $user = Mockery::mock(Authenticatable::class);

        $user->shouldReceive('hasVerifiedEmail')->andReturn(true);
        $user->shouldReceive('getAuthIdentifier')->andReturn(1);
        $user->shouldReceive('sendEmailVerificationNotification')->never();

        $response = $this->from('/email/verify')
                        ->actingAs($user)
                        ->post('/email/verification-notification');

        $response->assertRedirect('/home');
    }

    public function test_user_is_redirect_to_intended_url_if_already_verified()
    {
        $user = Mockery::mock(Authenticatable::class);

        $user->shouldReceive('hasVerifiedEmail')->andReturn(true);
        $user->shouldReceive('getAuthIdentifier')->andReturn(1);
        $user->shouldReceive('sendEmailVerificationNotification')->never();

        $response = $this->from('/email/verify')
                        ->actingAs($user)
                        ->withSession(['url.intended' => 'http://foo.com/bar'])
                        ->post('/email/verification-notification');

        $response->assertRedirect('http://foo.com/bar');
    }
}
