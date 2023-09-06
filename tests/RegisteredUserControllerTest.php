<?php

namespace Laravel\Fortify\Tests;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\StatefulGuard;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Fortify\Contracts\RegisterViewResponse;
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
        $this->mock(CreatesNewUsers::class)
                    ->shouldReceive('create')
                    ->andReturn(Mockery::mock(Authenticatable::class));

        $this->mock(StatefulGuard::class)
                    ->shouldReceive('login')
                    ->once();

        $response = $this->post('/register', []);

        $response->assertRedirect('/home');
    }

    public function test_users_can_be_created_and_redirected_to_intended_url()
    {
        $this->mock(CreatesNewUsers::class)
                    ->shouldReceive('create')
                    ->andReturn(Mockery::mock(Authenticatable::class));

        $this->mock(StatefulGuard::class)
                    ->shouldReceive('login')
                    ->once();

        $response = $this->withSession(['url.intended' => 'http://foo.com/bar'])
                        ->post('/register', []);

        $response->assertRedirect('http://foo.com/bar');
    }

    public function test_usernames_will_be_stored_case_insensitive()
    {
        app('config')->set('fortify.lowercase_usernames', true);

        $this->mock(CreatesNewUsers::class)
                    ->shouldReceive('create')
                    ->with([
                        'email' => 'taylor@laravel.com',
                        'password' => 'password',
                    ])
                    ->once()
                    ->andReturn(Mockery::mock(Authenticatable::class));

        $this->mock(StatefulGuard::class)
                    ->shouldReceive('login')
                    ->once();

        $response = $this->post('/register', [
            'email' => 'TAYLOR@LARAVEL.COM',
            'password' => 'password',
        ]);

        $response->assertRedirect('/home');
    }
}
