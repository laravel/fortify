<?php

namespace Laravel\Fortify\Tests;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Fortify;

class FortifyServiceProviderTest extends OrchestraTestCase
{
    public function test_views_can_be_customized()
    {
        Fortify::loginView(function () {
            return 'foo';
        });

        $response = $this->get('/login');

        $response->assertOk();
        $this->assertSame('foo', $response->content());
    }

    public function test_customized_views_can_return_their_own_responsable()
    {
        Fortify::loginView(function () {
            return new class implements Responsable
            {
                public function toResponse($request)
                {
                    return new JsonResponse(['foo' => 'bar']);
                }
            };
        });

        $response = $this->get('/login');

        $response->assertOk();
        $response->assertExactJson(['foo' => 'bar']);
    }
}
