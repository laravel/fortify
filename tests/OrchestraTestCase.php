<?php

namespace Laravel\Fortify\Tests;

use Laravel\Fortify\FortifyServiceProvider;
use Mockery;
use Orchestra\Testbench\TestCase;

abstract class OrchestraTestCase extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
    }

    protected function getPackageProviders($app)
    {
        return [FortifyServiceProvider::class];
    }
}
