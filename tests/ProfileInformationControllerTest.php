<?php

namespace Laravel\Fortify\Tests;

use App\Actions\Fortify\UpdateUserProfileInformation;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use Orchestra\Testbench\Attributes\WithMigration;

#[WithMigration]
class ProfileInformationControllerTest extends OrchestraTestCase
{
    use RefreshDatabase;

    public function test_contact_information_can_be_updated()
    {
        $user = UserFactory::new()->create();

        $this->double(UpdatesUserProfileInformation::class, UpdateUserProfileInformation::class)
            ->expects('update');

        $response = $this->withoutExceptionHandling()->actingAs($user)->putJson('/user/profile-information', [
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
        ]);

        $response->assertStatus(200);
    }

    public function test_email_address_will_be_updated_case_insensitive()
    {
        app('config')->set('fortify.lowercase_usernames', true);

        $user = UserFactory::new()->create();

        $this->double(UpdatesUserProfileInformation::class, UpdateUserProfileInformation::class)
            ->expects('update')
            ->with($user, [
                'name' => 'Taylor Otwell',
                'email' => 'taylor@laravel.com',
            ]);

        $response = $this->withoutExceptionHandling()->actingAs($user)->putJson('/user/profile-information', [
            'name' => 'Taylor Otwell',
            'email' => 'TAYLOR@LARAVEL.COM',
        ]);

        $response->assertStatus(200);
    }
}
