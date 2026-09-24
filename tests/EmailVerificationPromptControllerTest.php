<?php

namespace Laravel\Fortify\Tests;

use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Contracts\VerifyEmailViewResponse;
use Orchestra\Testbench\Attributes\WithMigration;

#[WithMigration]
class EmailVerificationPromptControllerTest extends OrchestraTestCase
{
    use RefreshDatabase;

    public function test_the_email_verification_prompt_view_is_returned()
    {
        $this->double(VerifyEmailViewResponse::class)
            ->allows('toResponse')
            ->returns(response('hello world'));

        $user = UserFactory::new()->unverified()->create();

        $response = $this->actingAs($user)->get('/email/verify');

        $response->assertStatus(200);
        $response->assertSeeText('hello world');
    }

    public function test_user_is_redirect_home_if_already_verified()
    {
        $this->double(VerifyEmailViewResponse::class)
            ->allows('toResponse')
            ->returns(response('hello world'));

        $user = UserFactory::new()->create();

        $response = $this->actingAs($user)->get('/email/verify');

        $response->assertRedirect('/home');
    }

    public function test_user_is_redirect_to_intended_url_if_already_verified()
    {
        $this->double(VerifyEmailViewResponse::class)
            ->allows('toResponse')
            ->returns(response('hello world'));

        $user = UserFactory::new()->create();

        $response = $this->actingAs($user)
            ->withSession(['url.intended' => 'http://foo.com/bar'])
            ->get('/email/verify');

        $response->assertRedirect('http://foo.com/bar');
    }
}
