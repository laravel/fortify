<?php

namespace Laravel\Fortify\Tests;

use Illuminate\Foundation\Auth\User;
use Laravel\Fortify\FortifyServiceProvider;

class RecoveryCodeControllerTest extends OrchestraTestCase
{
    public function test_new_recovery_codes_can_be_generated()
    {
        $this->loadLaravelMigrations(['--database' => 'testbench']);
        $this->artisan('migrate', ['--database' => 'testbench'])->run();

        $user = TestTwoFactorRecoveryCodeUser::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => bcrypt('secret'),
        ]);

        $response = $this->withoutExceptionHandling()->actingAs($user)->postJson(
            '/user/two-factor-recovery-codes'
        );

        $response->assertStatus(200);

        $user->fresh();

        $this->assertNotNull($user->two_factor_recovery_codes);
        $this->assertTrue(is_array(json_decode(decrypt($user->two_factor_recovery_codes), true)));
    }

    protected function getPackageProviders($app)
    {
        return [FortifyServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['migrator']->path(__DIR__.'/../database/migrations');

        $app['config']->set('database.default', 'testbench');

        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }
}

class TestTwoFactorRecoveryCodeUser extends User
{
    protected $table = 'users';
}
