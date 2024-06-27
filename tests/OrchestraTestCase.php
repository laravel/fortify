<?php

namespace Laravel\Fortify\Tests;

use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Laravel\Fortify\Features;
use Orchestra\Testbench\Attributes\WithConfig;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase;

#[WithConfig('database.default', 'testing')]
abstract class OrchestraTestCase extends TestCase
{
    use WithWorkbench;

    protected function withTwoFactorAuthentication($app)
    {
        $app['config']->set('fortify.features', [
            Features::twoFactorAuthentication(),
        ]);
    }

    protected function withConfirmedTwoFactorAuthentication($app)
    {
        $app['config']->set('fortify.features', [
            Features::twoFactorAuthentication(['confirm' => true]),
        ]);
    }

    protected function withoutTwoFactorAuthentication($app)
    {
        tap($app['config'], function ($config) {
            $features = $config->get('fortify.features');

            unset($features[array_search(Features::twoFactorAuthentication(), $features)]);

            $config->set('fortify.features', $features);
        });
    }
}
