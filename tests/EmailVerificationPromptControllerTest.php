<?php

namespace Laravel\Fortify\Tests;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Fortify\Contracts\VerifyEmailViewResponse;
use Mockery;

class EmailVerificationPromptControllerTest extends OrchestraTestCase
{
    public function test_the_email_verification_prompt_view_is_returned()
    {
        $this->mock(VerifyEmailViewResponse::class)
                ->shouldReceive('toResponse')
                ->andReturn(response('hello world'));

        $user = Mockery::mock(Authenticatable::class);
        $user->shouldReceive('hasVerifiedEmail')->andReturn(false);

        $response = $this->actingAs($user)->get('/email/verify');

        $response->assertStatus(200);
        $response->assertSeeText('hello world');
    }
}
