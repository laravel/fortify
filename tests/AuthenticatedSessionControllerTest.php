<?php

namespace Laravel\Fortify\Tests;

use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Laravel\Fortify\Contracts\LoginViewResponse;
use Laravel\Fortify\Events\TwoFactorAuthenticationChallenged;
use Laravel\Fortify\Features;
use Laravel\Fortify\LoginRateLimiter;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Mockery;
use PragmaRX\Google2FA\Google2FA;

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
        TestAuthenticationSessionUser::forceCreate([
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

    public function test_user_is_redirected_to_challenge_when_using_two_factor_authentication()
    {
        Event::fake();

        app('config')->set('auth.providers.users.model', TestTwoFactorAuthenticationSessionUser::class);

        TestTwoFactorAuthenticationSessionUser::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => bcrypt('secret'),
            'two_factor_secret' => 'test-secret',
        ]);

        $response = $this->withoutExceptionHandling()->post('/login', [
            'email' => 'taylor@laravel.com',
            'password' => 'secret',
        ]);

        $response->assertRedirect('/two-factor-challenge');

        Event::assertDispatched(TwoFactorAuthenticationChallenged::class);
    }

    public function test_user_is_not_redirected_to_challenge_when_using_two_factor_authentication_that_has_not_been_confirmed_and_confirmation_is_enabled()
    {
        Event::fake();

        app('config')->set('auth.providers.users.model', TestTwoFactorAuthenticationSessionUser::class);
        app('config')->set('fortify.features', [
            Features::registration(),
            Features::twoFactorAuthentication(['confirm' => true]),
        ]);

        TestTwoFactorAuthenticationSessionUser::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => bcrypt('secret'),
            'two_factor_secret' => 'test-secret',
        ]);

        $response = $this->withoutExceptionHandling()->post('/login', [
            'email' => 'taylor@laravel.com',
            'password' => 'secret',
        ]);

        $response->assertRedirect('/home');
    }

    public function test_user_is_redirected_to_challenge_when_using_two_factor_authentication_that_has_been_confirmed_and_confirmation_is_enabled()
    {
        Event::fake();

        app('config')->set('auth.providers.users.model', TestTwoFactorAuthenticationSessionUser::class);
        app('config')->set('fortify.features', [
            Features::registration(),
            Features::twoFactorAuthentication(['confirm' => true]),
        ]);

        Schema::table('users', function ($table) {
            $table->timestamp('two_factor_confirmed_at')->nullable();
        });

        TestTwoFactorAuthenticationSessionUser::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => bcrypt('secret'),
            'two_factor_secret' => 'test-secret',
            'two_factor_confirmed_at' => now(),
        ]);

        $response = $this->withoutExceptionHandling()->post('/login', [
            'email' => 'taylor@laravel.com',
            'password' => 'secret',
        ]);

        $response->assertRedirect('/two-factor-challenge');
    }

    public function test_user_can_authenticate_when_two_factor_challenge_is_disabled()
    {
        app('config')->set('auth.providers.users.model', TestTwoFactorAuthenticationSessionUser::class);

        $features = app('config')->get('fortify.features');

        unset($features[array_search(Features::twoFactorAuthentication(), $features)]);

        app('config')->set('fortify.features', $features);

        TestTwoFactorAuthenticationSessionUser::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => bcrypt('secret'),
            'two_factor_secret' => 'test-secret',
        ]);

        $response = $this->withoutExceptionHandling()->post('/login', [
            'email' => 'taylor@laravel.com',
            'password' => 'secret',
        ]);

        $response->assertRedirect('/home');
    }

    public function test_validation_exception_returned_on_failure()
    {
        TestAuthenticationSessionUser::forceCreate([
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

    public function usernameProvider(): array
    {
        return [
            'lowercase special characters' => ['ⓣⓔⓢⓣ@ⓛⓐⓡⓐⓥⓔⓛ.ⓒⓞⓜ', 'test@laravel.com'],
            'uppercase special characters' => ['ⓉⒺⓈⓉ@ⓁⒶⓇⒶⓋⒺⓁ.ⒸⓄⓂ', 'test@laravel.com'],
            'special character numbers' =>['test⑩⓸③@laravel.com', 'test1043@laravel.com'],
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

    public function test_two_factor_challenge_can_be_passed_via_code()
    {
        app('config')->set('auth.providers.users.model', TestTwoFactorAuthenticationSessionUser::class);

        $tfaEngine = app(Google2FA::class);
        $userSecret = $tfaEngine->generateSecretKey();
        $validOtp = $tfaEngine->getCurrentOtp($userSecret);

        $user = TestTwoFactorAuthenticationSessionUser::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => bcrypt('secret'),
            'two_factor_secret' => encrypt($userSecret),
        ]);

        $response = $this->withSession([
            'login.id' => $user->id,
            'login.remember' => false,
        ])->withoutExceptionHandling()->post('/two-factor-challenge', [
            'code' => $validOtp,
        ]);

        $response->assertRedirect('/home')
            ->assertSessionMissing('login.id');
    }

    public function test_two_factor_authentication_preserves_remember_me_selection(): void
    {
        Event::fake();

        app('config')->set('auth.providers.users.model', TestTwoFactorAuthenticationSessionUser::class);

        TestTwoFactorAuthenticationSessionUser::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => bcrypt('secret'),
            'two_factor_secret' => 'test-secret',
        ]);

        $response = $this->withoutExceptionHandling()->post('/login', [
            'email' => 'taylor@laravel.com',
            'password' => 'secret',
            'remember' => false,
        ]);

        $response->assertRedirect('/two-factor-challenge')
            ->assertSessionHas('login.remember', false);
    }

    public function test_two_factor_challenge_fails_for_old_otp_and_zero_window()
    {
        app('config')->set('auth.providers.users.model', TestTwoFactorAuthenticationSessionUser::class);

        //Setting window to 0 should mean any old OTP is instantly invalid
        app('config')->set('fortify.features', [
            Features::twoFactorAuthentication(['window' => 0]),
        ]);

        $tfaEngine = app(Google2FA::class);
        $userSecret = $tfaEngine->generateSecretKey();
        $currentTs = $tfaEngine->getTimestamp();
        $previousOtp = $tfaEngine->oathTotp($userSecret, $currentTs - 1);

        $user = TestTwoFactorAuthenticationSessionUser::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => bcrypt('secret'),
            'two_factor_secret' => encrypt($userSecret),
        ]);

        $response = $this->withSession([
            'login.id' => $user->id,
            'login.remember' => false,
        ])->withoutExceptionHandling()->post('/two-factor-challenge', [
            'code' => $previousOtp,
        ]);

        $response->assertRedirect('/two-factor-challenge')
                 ->assertSessionHas('login.id')
                 ->assertSessionHasErrors(['code']);
    }

    public function test_two_factor_challenge_can_be_passed_via_recovery_code()
    {
        app('config')->set('auth.providers.users.model', TestTwoFactorAuthenticationSessionUser::class);

        $user = TestTwoFactorAuthenticationSessionUser::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => bcrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['invalid-code', 'valid-code'])),
        ]);

        $response = $this->withSession([
            'login.id' => $user->id,
            'login.remember' => false,
        ])->withoutExceptionHandling()->post('/two-factor-challenge', [
            'recovery_code' => 'valid-code',
        ]);

        $response->assertRedirect('/home')
            ->assertSessionMissing('login.id');
        $this->assertNotNull(Auth::getUser());
        $this->assertNotContains('valid-code', json_decode(decrypt($user->fresh()->two_factor_recovery_codes), true));
    }

    public function test_two_factor_challenge_can_fail_via_recovery_code()
    {
        app('config')->set('auth.providers.users.model', TestTwoFactorAuthenticationSessionUser::class);

        $user = TestTwoFactorAuthenticationSessionUser::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => bcrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['invalid-code', 'valid-code'])),
        ]);

        $response = $this->withSession([
            'login.id' => $user->id,
            'login.remember' => false,
        ])->withoutExceptionHandling()->post('/two-factor-challenge', [
            'recovery_code' => 'missing-code',
        ]);

        $response->assertRedirect('/two-factor-challenge')
            ->assertSessionHas('login.id')
            ->assertSessionHasErrors(['recovery_code']);
        $this->assertNull(Auth::getUser());
    }

    public function test_two_factor_challenge_requires_a_challenged_user()
    {
        app('config')->set('auth.providers.users.model', TestTwoFactorAuthenticationSessionUser::class);

        $response = $this->withSession([])->withoutExceptionHandling()->get('/two-factor-challenge');

        $response->assertRedirect('/login');
        $this->assertNull(Auth::getUser());
    }

    public function test_case_insensitive_usernames_can_be_used()
    {
        app('config')->set('fortify.lowercase_usernames', true);

        TestAuthenticationSessionUser::forceCreate([
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

    protected function defineEnvironment($app)
    {
        parent::defineEnvironment($app);

        $app['config']->set([
            'auth.providers.users.model' => TestAuthenticationSessionUser::class,
        ]);
    }
}

class TestAuthenticationSessionUser extends User
{
    protected $table = 'users';
}

class TestTwoFactorAuthenticationSessionUser extends User
{
    use TwoFactorAuthenticatable;

    protected $table = 'users';
}
