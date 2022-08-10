<?php

namespace Laravel\Fortify\Tests;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\StatefulGuard;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Fortify\Contracts\RegisterViewResponse;
use Laravel\Fortify\Contracts\UserRegistrationRequest;
use Mockery;

class RegisteredUserControllerTest extends OrchestraTestCase
{
    public function test_the_register_view_is_returned()
    {
        $this->mock(RegisterViewResponse::class)
                ->shouldReceive('toResponse')
                ->andReturn(response('hello world'));

        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSeeText('hello world');
    }

    public function test_users_can_be_created()
    {
        $request = $this->mock(UserRegistrationRequest::class);
        $request->shouldReceive('validated')->andReturn([]);

        $this->app->singleton(UserRegistrationRequest::class, function () use ($request) {
            return $request;
        });

        $this->mock(CreatesNewUsers::class)
                    ->shouldReceive('create')
                    ->andReturn(Mockery::mock(Authenticatable::class));

        $this->mock(StatefulGuard::class)
                    ->shouldReceive('login')
                    ->once();

        $response = $this->post('/register', [
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => 'secret',
        ]);

        $response->assertRedirect('/home');
    }

    public function test_users_can_be_created_and_redirected_to_intended_url()
    {
        $request = $this->mock(UserRegistrationRequest::class);
        $request->shouldReceive('validated')->andReturn([]);

        $this->app->singleton(UserRegistrationRequest::class, function () use ($request) {
            return $request;
        });

        $this->mock(CreatesNewUsers::class)
                    ->shouldReceive('create')
                    ->andReturn(Mockery::mock(Authenticatable::class));

        $this->mock(StatefulGuard::class)
                    ->shouldReceive('login')
                    ->once();

        $response = $this->withSession(['url.intended' => 'http://foo.com/bar'])
                        ->post('/register', [
                            'name' => 'Taylor Otwell',
                            'email' => 'taylor@laravel.com',
                            'password' => 'secret',
                        ]);

        $response->assertRedirect('http://foo.com/bar');
    }
}
