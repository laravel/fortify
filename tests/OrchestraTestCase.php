<?php

namespace Laravel\Fortify\Tests;

use JMac\Testing\Double;
use JMac\Testing\Integrations\PHPUnit\VerifiesDoubles;
use Laravel\Fortify\Features;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase;

abstract class OrchestraTestCase extends TestCase
{
    use VerifiesDoubles;

    use WithWorkbench;

    /**
     * Register a test double for the given abstract in the container.
     *
     * @param  class-string  $abstract
     * @param  class-string|null  $target
     * @return \JMac\Testing\DoubleInterface
     */
    protected function double(string $abstract, ?string $target = null): object
    {
        return $this->instance($abstract, Double::for($target ?? $abstract));
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('database.default', 'testing');
    }

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

            if (($key = array_search(Features::twoFactorAuthentication(), $features)) !== false) {
                unset($features[$key]);
            }

            $config->set('fortify.features', array_values($features));
        });
    }

    protected function withPasskeys($app)
    {
        $app['config']->set('fortify.features', [
            Features::passkeys(),
        ]);
    }

    protected function withPasskeysConfirmingPasswords($app)
    {
        $app['config']->set('fortify.features', [
            Features::passkeys(['confirmPassword' => true]),
        ]);
    }

    protected function withPasskeysWithoutPasswordConfirmation($app)
    {
        $app['config']->set('fortify.features', [
            Features::passkeys(['confirmPassword' => false]),
        ]);
    }

    protected function withPasskeysLimiter($app)
    {
        $app['config']->set('fortify.features', [
            Features::passkeys(),
        ]);

        $app['config']->set('fortify.limiters.passkeys', 'passkeys');
    }

    protected function withoutPasskeys($app)
    {
        tap($app['config'], function ($config) {
            $features = $config->get('fortify.features');

            if (($key = array_search(Features::passkeys(), $features)) !== false) {
                unset($features[$key]);
            }

            $config->set('fortify.features', array_values($features));
        });
    }
}
