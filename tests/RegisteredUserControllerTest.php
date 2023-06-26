<?php

namespace Laravel\Fortify\Tests;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\User;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Fortify\Contracts\RegisterViewResponse;
use Laravel\Fortify\FortifyServiceProvider;
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

        $this->loadLaravelMigrations(['--database' => 'testbench']);

        TestRegisterUser::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => bcrypt('secret'),
        ]);


        $response = $this->post('/register', [
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => 'secret',
        ]);

        $response->assertRedirect('/home');
    }

    public function test_users_can_be_created_and_redirected_to_intended_url()
    {
        $this->mock(CreatesNewUsers::class)
                    ->shouldReceive('create')
                    ->andReturn(Mockery::mock(Authenticatable::class));

        $this->loadLaravelMigrations(['--database' => 'testbench']);

        TestRegisterUser::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => bcrypt('secret'),
        ]);

        $response = $this->withSession(['url.intended' => 'http://foo.com/bar'])->post('/register', [
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => 'secret',
        ]);

        $response->assertRedirect('http://foo.com/bar');
    }

    protected function getPackageProviders($app)
    {
        return [FortifyServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['migrator']->path(__DIR__.'/../database/migrations');

        $app['config']->set('auth.providers.users.model', TestRegisterUser::class);

        $app['config']->set('database.default', 'testbench');

        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }
}

class TestRegisterUser extends User
{
    protected $table = 'users';
}

