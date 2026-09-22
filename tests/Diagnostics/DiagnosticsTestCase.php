<?php

namespace Laravel\Fortify\Tests\Diagnostics;

use Laravel\Doctor\DoctorServiceProvider;
use Laravel\Fortify\Tests\OrchestraTestCase;

abstract class DiagnosticsTestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        if (! class_exists(DoctorServiceProvider::class)) {
            $this->markTestSkipped('laravel/doctor is not installed.');
        }

        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return array_merge(parent::getPackageProviders($app), [
            DoctorServiceProvider::class,
        ]);
    }
}
