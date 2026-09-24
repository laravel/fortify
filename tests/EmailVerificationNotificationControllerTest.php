<?php

namespace Laravel\Fortify\Tests;

use JMac\Testing\Double;
use Illuminate\Contracts\Auth\Authenticatable;
use Mockery;

class EmailVerificationNotificationControllerTest extends OrchestraTestCase
{
    public function test_email_verification_notification_can_be_sent()
    {
        $user = Double::for(Authenticatable::class);

        $user->allows('hasVerifiedEmail')->returns(false);
        $user->allows('getAuthIdentifier')->returns(1);
        $user->expects('sendEmailVerificationNotification');

        $response = $this->from('/email/verify')
                        ->actingAs($user)
                        ->post('/email/verification-notification');

        $response->assertRedirect('/email/verify');
    }

    public function test_user_is_redirect_if_already_verified()
    {
        $user = Double::for(Authenticatable::class);

        $user->allows('hasVerifiedEmail')->returns(true);
        $user->allows('getAuthIdentifier')->returns(1);
        $user->expects('sendEmailVerificationNotification')->never();

        $response = $this->from('/email/verify')
                        ->actingAs($user)
                        ->post('/email/verification-notification');

        $response->assertRedirect('/home');
    }

    public function test_user_is_redirect_to_intended_url_if_already_verified()
    {
        $user = Double::for(Authenticatable::class);

        $user->allows('hasVerifiedEmail')->returns(true);
        $user->allows('getAuthIdentifier')->returns(1);
        $user->expects('sendEmailVerificationNotification')->never();

        $response = $this->from('/email/verify')
                        ->actingAs($user)
                        ->withSession(['url.intended' => 'http://foo.com/bar'])
                        ->post('/email/verification-notification');

        $response->assertRedirect('http://foo.com/bar');
    }
}
