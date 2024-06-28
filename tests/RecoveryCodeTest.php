<?php

namespace Laravel\Fortify\Tests;

use Illuminate\Support\Str;
use Laravel\Fortify\RecoveryCode;

class RecoveryCodeTest extends OrchestraTestCase
{
    public function test_recovery_codes_can_be_generated_with_a_custom_generator()
    {
        Str::createRandomStringsUsingSequence(['123', 'abc']);

        RecoveryCode::generateRecoveryCodeUsing(function () {
            return Str::random().'-'.Str::random();
        });

        $this->assertEquals('123-abc', RecoveryCode::generate());
    }
}
