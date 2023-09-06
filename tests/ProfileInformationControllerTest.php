<?php

namespace Laravel\Fortify\Tests;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use Mockery;

class ProfileInformationControllerTest extends OrchestraTestCase
{
    public function test_contact_information_can_be_updated()
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

    public function test_email_address_will_be_updated_case_insensitive()
    {
        app('config')->set('fortify.lowercase_usernames', true);

        $user = Mockery::mock(Authenticatable::class);

        $this->mock(UpdatesUserProfileInformation::class)
                    ->shouldReceive('update')
                    ->with($user, [
                        'name' => 'Taylor Otwell',
                        'email' => 'taylor@laravel.com',
                    ])
                    ->once();

        $response = $this->withoutExceptionHandling()->actingAs($user)->putJson('/user/profile-information', [
            'name' => 'Taylor Otwell',
            'email' => 'TAYLOR@LARAVEL.COM',
        ]);

        $response->assertStatus(200);
    }
}
