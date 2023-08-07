<?php

namespace Laravel\Fortify\Tests;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use Mockery;

class ProfileInformationControllerTest extends OrchestraTestCase
{
    public function testContactInformationCanBeUpdated()
    {
        $user = Mockery::mock(Authenticatable::class);

        $this->mock(UpdatesUserProfileInformation::class)
                    ->shouldReceive('update')
                    ->once();

        $response = $this->withoutExceptionHandling()->actingAs($user)->putJson('/user/profile-information', [
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
        ]);

        $response->assertStatus(200);
    }
}
