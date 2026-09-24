<?php

namespace Laravel\Fortify\Tests;

use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Fortify\Fortify;
use Orchestra\Testbench\Attributes\WithMigration;

#[WithMigration]
class RegisteredUserControllerTest extends OrchestraTestCase
{
    use RefreshDatabase;

    public function test_the_register_view_is_returned()
    {
        Fortify::registerView(fn () => 'hello world');

        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSeeText('hello world');
    }

    public function test_users_can_be_created()
    {
        $this->double(CreatesNewUsers::class)
            ->allows('create')
            ->returns($user = UserFactory::new()->create());

        $response = $this->post('/register', []);

        $response->assertRedirect('/home');
        $this->assertAuthenticatedAs($user);
    }

    public function test_users_can_be_created_and_redirected_to_intended_url()
    {
        $this->double(CreatesNewUsers::class)
            ->allows('create')
            ->returns($user = UserFactory::new()->create());

        $response = $this->withSession(['url.intended' => 'http://foo.com/bar'])
            ->post('/register', []);

        $response->assertRedirect('http://foo.com/bar');
        $this->assertAuthenticatedAs($user);
    }

    public function test_usernames_will_be_stored_case_insensitive()
    {
        app('config')->set('fortify.lowercase_usernames', true);

        $this->double(CreatesNewUsers::class)
            ->expects('create')
            ->with([
                'email' => 'taylor@laravel.com',
                'password' => 'password',
            ])
            ->returns($user = UserFactory::new()->create());

        $response = $this->post('/register', [
            'email' => 'TAYLOR@LARAVEL.COM',
            'password' => 'password',
        ]);

        $response->assertRedirect('/home');
        $this->assertAuthenticatedAs($user);
    }

    public function test_users_can_be_created_with_remember_option()
    {
        $this->double(CreatesNewUsers::class)
            ->expects('create')
            ->returns($user = UserFactory::new()->create());

        $response = $this->post('/register', [
            'email' => 'taylor@laravel.com',
            'password' => 'password',
            'remember' => '1',
        ]);

        $response->assertRedirect('/home');
        $this->assertAuthenticatedAs($user);
        $response->assertCookie(Auth::guard()->getRecallerName());
    }
}
