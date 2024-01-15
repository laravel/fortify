<?php

namespace Laravel\Fortify\Tests;

use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Laravel\Fortify\Features;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase;

abstract class OrchestraTestCase extends TestCase
{
    use WithWorkbench;

    public function setUp(): void
    {
        if (class_exists(RefreshDatabaseState::class)) {
            RefreshDatabaseState::$migrated = false;
        }

        parent::setUp();
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set(['database.default' => 'testing']);
    }

    protected function withConfirmedTwoFactorAuthentication($app)
    {
        $app['config']->set('fortify.features', [
            Features::twoFactorAuthentication(['confirm' => true]),
        ]);
    }
}
