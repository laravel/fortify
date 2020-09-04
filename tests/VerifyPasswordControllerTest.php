<?php

namespace Laravel\Fortify\Tests;

use Mockery;
use Illuminate\Foundation\Auth\User;
use Laravel\Fortify\Contracts\VerifyPasswordViewResponse;

class VerifyPasswordControllerTest extends OrchestraTestCase
{
    protected $user;

    public function test_the_verify_password_view_is_returned()
    {
        $this->mock(VerifyPasswordViewResponse::class)
            ->shouldReceive('toResponse')
            ->andReturn(response('hello world'));


        $response = $this->withoutExceptionHandling()->actingAs($this->user)->get(
            '/user/password/verify'
        );

        $response->assertStatus(200);
        $response->assertSeeText('hello world');
    }

    public function test_password_can_be_verified()
    {
        $response = $this->withoutExceptionHandling()
            ->actingAs($this->user)
            ->withSession(['url.intended' => 'http://foo.com/bar'])
            ->post(
                '/user/password/verify',
                ['password' => 'secret',]
            );

        $response->assertSessionHas('auth.password_confirmed_at');
        $response->assertRedirect('http://foo.com/bar');
    }

    public function test_password_verification_can_fail()
    {
        $response = $this->withoutExceptionHandling()
            ->actingAs($this->user)
            ->withSession(['url.intended' => 'http://foo.com/bar'])
            ->post(
                '/user/password/verify',
                ['password' => 'invalid',]
            );

        $response->assertSessionHasErrors(['password']);
        $response->assertSessionMissing('auth.password_confirmed_at');
        $response->assertRedirect();
        $this->assertNotEquals($response->getTargetUrl(), 'http://foo.com/bar');
    }

    public function test_password_can_be_verified_with_json()
    {
        $response = $this->actingAs($this->user)
            ->postJson(
                '/user/password/verify',
                ['password' => 'secret',]
            );
        $response->assertStatus(201);
    }

    public function test_password_verification_can_fail_with_json()
    {
        $response = $this->actingAs($this->user)
            ->postJson(
                '/user/password/verify',
                ['password' => 'invalid',]
            );

        $response->assertJsonValidationErrors('password');
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadLaravelMigrations(['--database' => 'testbench']);
        $this->artisan('migrate', ['--database' => 'testbench'])->run();

        $this->user = TestVerifyPasswordUser::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => bcrypt('secret'),
        ]);
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['migrator']->path(__DIR__.'/../database/migrations');

        $app['config']->set('auth.providers.users.model', TestVerifyPasswordUser::class);

        $app['config']->set('database.default', 'testbench');

        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }
}

class TestVerifyPasswordUser extends User
{
    protected $table = 'users';
}
