<?php

namespace Laravel\Fortify\Tests\Diagnostics;

use Laravel\Doctor\DoctorServiceProvider;
use Laravel\Fortify\Tests\OrchestraTestCase;

abstract class DiagnosticsTestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return array_merge(parent::getPackageProviders($app), [
            DoctorServiceProvider::class,
        ]);
    }
}
