<?php

namespace Laravel\Fortify\Tests;

use Illuminate\Auth\Events\Logout;
use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Laravel\Fortify\Contracts\LoginViewResponse;
use Laravel\Fortify\LoginRateLimiter;
use Mockery;
use Orchestra\Testbench\Attributes\WithMigration;

#[WithMigration]
class AuthenticatedSessionControllerTest extends OrchestraTestCase
{
    use RefreshDatabase;

    public function test_the_login_view_is_returned()
    {
        $this->mock(LoginViewResponse::class)
                ->shouldReceive('toResponse')
                ->andReturn(response('hello world'));

        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSeeText('hello world');
    }

    public function test_user_can_authenticate()
    {
        User::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => bcrypt('secret'),
        ]);

        $response = $this->withoutExceptionHandling()->post('/login', [
            'email' => 'taylor@laravel.com',
            'password' => 'secret',
        ]);

        $response->assertRedirect('/home');
    }

    public function test_validation_exception_returned_on_failure()
    {
        User::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => bcrypt('secret'),
        ]);

        $response = $this->post('/login', [
            'email' => 'taylor@laravel.com',
            'password' => 'password',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email']);
    }

    public function test_login_attempts_are_throttled()
    {
        $this->mock(LoginRateLimiter::class, function ($mock) {
            $mock->shouldReceive('tooManyAttempts')->andReturn(true);
            $mock->shouldReceive('availableIn')->andReturn(10);
        });

        $response = $this->postJson('/login', [
            'email' => 'taylor@laravel.com',
            'password' => 'secret',
        ]);

        $response->assertStatus(429);
        $response->assertJsonValidationErrors(['email']);
    }

    /**
     * @dataProvider usernameProvider
     */
    public function test_cant_bypass_throttle_with_special_characters(string $username, string $expectedResult)
    {
        $loginRateLimiter = new LoginRateLimiter(
            $this->mock(RateLimiter::class)
        );

        $reflection = new \ReflectionClass($loginRateLimiter);
        $method = $reflection->getMethod('throttleKey');
        $method->setAccessible(true);

        $request = $this->mock(
            Request::class,
            static function ($mock) use ($username) {
                $mock->shouldReceive('input')->andReturn($username);
                $mock->shouldReceive('ip')->andReturn('192.168.0.1');
            }
        );

        self::assertSame($expectedResult.'|192.168.0.1', $method->invoke($loginRateLimiter, $request));
    }

    public static function usernameProvider(): array
    {
        return [
            'lowercase special characters' => ['ⓣⓔⓢⓣ@ⓛⓐⓡⓐⓥⓔⓛ.ⓒⓞⓜ', 'test@laravel.com'],
            'uppercase special characters' => ['ⓉⒺⓈⓉ@ⓁⒶⓇⒶⓋⒺⓁ.ⒸⓄⓂ', 'test@laravel.com'],
            'special character numbers' => ['test⑩⓸③@laravel.com', 'test1043@laravel.com'],
            'default email' => ['test@laravel.com', 'test@laravel.com'],
        ];
    }

    public function test_the_user_can_logout_of_the_application()
    {
        Auth::guard()->setUser(
            Mockery::mock(Authenticatable::class)->shouldIgnoreMissing()
        );

        $response = $this->post('/logout');

        $response->assertRedirect('/');
        $this->assertNull(Auth::guard()->getUser());
    }

    public function test_the_user_can_logout_of_the_application_using_json_request()
    {
        Auth::guard()->setUser(
            Mockery::mock(Authenticatable::class)->shouldIgnoreMissing()
        );

        $response = $this->postJson('/logout');

        $response->assertStatus(204);
        $this->assertNull(Auth::guard()->getUser());
    }

    public function test_case_insensitive_usernames_can_be_used()
    {
        app('config')->set('fortify.lowercase_usernames', true);

        User::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => bcrypt('secret'),
        ]);

        $response = $this->withoutExceptionHandling()->post('/login', [
            'email' => 'TAYLOR@LARAVEL.COM',
            'password' => 'secret',
        ]);

        $response->assertRedirect('/home');
    }

    public function test_users_can_logout(): void
    {
        $user = User::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => bcrypt('secret'),
        ]);
        Event::fake([Logout::class]);

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect();
        $this->assertGuest();
        Event::assertDispatched(fn (Logout $logout) => $logout->user->is($user));
    }

    public function test_must_be_authenticated_to_logout(): void
    {
        Event::fake([Logout::class]);

        $response = $this->post('/logout');

        $response->assertRedirect();
        $this->assertGuest();
        Event::assertNotDispatched(Logout::class);
    }
}
