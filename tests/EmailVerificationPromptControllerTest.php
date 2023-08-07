<?php

namespace Laravel\Fortify\Tests;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Fortify\Contracts\VerifyEmailViewResponse;
use Mockery;

class EmailVerificationPromptControllerTest extends OrchestraTestCase
{
    public function testTheEmailVerificationPromptViewIsReturned()
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

    public function testUserIsRedirectHomeIfAlreadyVerified()
    {
        $this->mock(VerifyEmailViewResponse::class)
                ->shouldReceive('toResponse')
                ->andReturn(response('hello world'));

        $user = Mockery::mock(Authenticatable::class);
        $user->shouldReceive('hasVerifiedEmail')->andReturn(true);

        $response = $this->actingAs($user)->get('/email/verify');

        $response->assertRedirect('/home');
    }

    public function testUserIsRedirectToIntendedUrlIfAlreadyVerified()
    {
        $this->mock(VerifyEmailViewResponse::class)
                ->shouldReceive('toResponse')
                ->andReturn(response('hello world'));

        $user = Mockery::mock(Authenticatable::class);
        $user->shouldReceive('hasVerifiedEmail')->andReturn(true);

        $response = $this->actingAs($user)
            ->withSession(['url.intended' => 'http://foo.com/bar'])
            ->get('/email/verify');

        $response->assertRedirect('http://foo.com/bar');
    }
}
