<?php

namespace Laravel\Fortify\Tests;

use Database\Factories\UserFactory;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Orchestra\Testbench\Attributes\WithMigration;

#[WithMigration]
class EmailVerificationNotificationControllerTest extends OrchestraTestCase
{
    use RefreshDatabase;

    public function test_email_verification_notification_can_be_sent()
    {
        Notification::fake();

        $user = UserFactory::new()->unverified()->create();

        $response = $this->from('/email/verify')
            ->actingAs($user)
            ->post('/email/verification-notification');

        $response->assertRedirect('/email/verify');
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_user_is_redirect_if_already_verified()
    {
        Notification::fake();

        $user = UserFactory::new()->create();

        $response = $this->from('/email/verify')
            ->actingAs($user)
            ->post('/email/verification-notification');

        $response->assertRedirect('/home');
        Notification::assertNothingSent();
    }

    public function test_user_is_redirect_to_intended_url_if_already_verified()
    {
        Notification::fake();

        $user = UserFactory::new()->create();

        $response = $this->from('/email/verify')
            ->actingAs($user)
            ->withSession(['url.intended' => 'http://foo.com/bar'])
            ->post('/email/verification-notification');

        $response->assertRedirect('http://foo.com/bar');
        Notification::assertNothingSent();
    }
}
