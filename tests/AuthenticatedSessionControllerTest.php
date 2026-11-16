<?php

namespace Laravel\Fortify\Tests;

use Database\Factories\UserFactory;
use Illuminate\Auth\Events\Logout;
use Illuminate\Cache\RateLimiter;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\LoginRateLimiter;
use Orchestra\Testbench\Attributes\RequiresLaravel;
use Orchestra\Testbench\Attributes\WithMigration;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;

#[WithMigration]
class AuthenticatedSessionControllerTest extends OrchestraTestCase
{
    use RefreshDatabase;

    public function test_the_login_view_is_returned()
    {
        Fortify::loginView(fn () => 'hello world');

        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSeeText('hello world');
    }

    #[TestWith([null])]
    #[TestWith([true])]
    #[TestWith([false])]
    public function test_user_can_authenticate(?bool $remember)
    {
        $this->assertUserCanBeAuthenticated($remember);
    }

    #[TestWith([null])]
    #[TestWith([true])]
    #[TestWith([false])]
    #[RequiresLaravel('>=13.4.0')]
    public function test_user_can_authenticate_using_failed_on_unknown_fields(?bool $remember)
    {
        FormRequest::failOnUnknownFields();

        $this->assertUserCanBeAuthenticated($remember);
    }

    protected function assertUserCanBeAuthenticated(?bool $remember = null)
    {
        User::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => bcrypt('secret'),
        ]);

        $response = $this->withoutExceptionHandling()->post('/login', array_filter([
            'email' => 'taylor@laravel.com',
            'password' => 'secret',
            'remember' => $remember,
        ]));

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
        foreach (range(1, 5) as $attempt) {
            $this->postJson('/login', [
                'email' => 'taylor@laravel.com',
                'password' => 'secret',
            ])->assertStatus(422);
        }

        $response = $this->postJson('/login', [
            'email' => 'taylor@laravel.com',
            'password' => 'secret',
        ]);

        $response->assertStatus(429);
        $response->assertJsonValidationErrors(['email']);
    }

    #[DataProvider('usernameProvider')]
    public function test_cant_bypass_throttle_with_special_characters(string $username, string $expectedResult)
    {
        $loginRateLimiter = new LoginRateLimiter(
            app(RateLimiter::class)
        );

        $reflection = new \ReflectionClass($loginRateLimiter);
        $method = $reflection->getMethod('throttleKey');
        $method->setAccessible(true);

        $request = Request::create('/login', 'POST', ['email' => $username], server: ['REMOTE_ADDR' => '192.168.0.1']);
        $canonical = Request::create('/login', 'POST', ['email' => $expectedResult], server: ['REMOTE_ADDR' => '192.168.0.1']);

        $key = $method->invoke($loginRateLimiter, $request);

        $this->assertSame($method->invoke($loginRateLimiter, $canonical), $key);
        $this->assertSame(hash_hmac('sha256', $expectedResult.'|192.168.0.1', config('app.key')), $key);
        $this->assertStringNotContainsString($expectedResult, $key);
        $this->assertStringNotContainsString('192.168.0.1', $key);
    }

    public function test_throttle_key_does_not_contain_the_username_or_ip()
    {
        $loginRateLimiter = new LoginRateLimiter(
            app(RateLimiter::class)
        );

        $reflection = new \ReflectionClass($loginRateLimiter);
        $method = $reflection->getMethod('throttleKey');

        $request = Request::create('/login', 'POST', ['email' => 'jane@example.com'], server: ['REMOTE_ADDR' => '203.0.113.10']);

        $key = $method->invoke($loginRateLimiter, $request);

        $this->assertSame(64, strlen($key));
        $this->assertStringNotContainsString('jane@example.com', $key);
        $this->assertStringNotContainsString('203.0.113.10', $key);

        $loginRateLimiter->increment($request);

        $this->assertSame(1, $loginRateLimiter->attempts($request));
        $this->assertNull(app('cache')->get('jane@example.com|203.0.113.10'));
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
        $user = UserFactory::new()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_the_user_can_logout_of_the_application_using_json_request()
    {
        $user = UserFactory::new()->create();

        $response = $this->actingAs($user)->postJson('/logout');

        $response->assertStatus(204);
        $this->assertGuest();
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
