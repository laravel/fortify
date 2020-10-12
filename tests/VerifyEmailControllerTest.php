<?php

namespace Laravel\Fortify\Tests;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\URL;
use Mockery;

class VerifyEmailControllerTest extends OrchestraTestCase
{
    public function test_the_email_can_be_verified()
    {
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => 1,
                'hash' => sha1('taylor@laravel.com'),
            ]
        );

        $user = Mockery::mock(Authenticatable::class);
        $user->shouldReceive('getKey')->andReturn(1);
        $user->shouldReceive('getAuthIdentifier')->andReturn(1);
        $user->shouldReceive('getEmailForVerification')->andReturn('taylor@laravel.com');
        $user->shouldReceive('hasVerifiedEmail')->andReturn(false);
        $user->shouldReceive('markEmailAsVerified')->once();

        $response = $this->actingAs($user)
            ->withSession(['url.intended' => 'http://foo.com/bar'])
            ->get($url);

        $response->assertRedirect('http://foo.com/bar');
    }

    public function test_redirected_if_email_is_already_verified()
    {
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => 1,
                'hash' => sha1('taylor@laravel.com'),
            ]
        );

        $user = Mockery::mock(Authenticatable::class);
        $user->shouldReceive('getKey')->andReturn(1);
        $user->shouldReceive('getAuthIdentifier')->andReturn(1);
        $user->shouldReceive('getEmailForVerification')->andReturn('taylor@laravel.com');
        $user->shouldReceive('hasVerifiedEmail')->andReturn(true);
        $user->shouldReceive('markEmailAsVerified')->never();

        $response = $this->actingAs($user)->get($url);

        $response->assertStatus(302);
    }

    public function test_email_is_not_verified_if_id_does_not_match()
    {
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => 2,
                'hash' => sha1('taylor@laravel.com'),
            ]
        );

        $user = Mockery::mock(Authenticatable::class);
        $user->shouldReceive('getKey')->andReturn(1);
        $user->shouldReceive('getAuthIdentifier')->andReturn(1);
        $user->shouldReceive('getEmailForVerification')->andReturn('taylor@laravel.com');

        $response = $this->actingAs($user)->get($url);

        $response->assertStatus(403);
    }

    public function test_email_is_not_verified_if_email_does_not_match()
    {
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => 1,
                'hash' => sha1('abigail@laravel.com'),
            ]
        );

        $user = Mockery::mock(Authenticatable::class);
        $user->shouldReceive('getKey')->andReturn(1);
        $user->shouldReceive('getAuthIdentifier')->andReturn(1);
        $user->shouldReceive('getEmailForVerification')->andReturn('taylor@laravel.com');

        $response = $this->actingAs($user)->get($url);

        $response->assertStatus(403);
    }
}
