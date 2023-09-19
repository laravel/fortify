<?php

namespace Laravel\Fortify\Tests;

use App\Actions\Fortify\UpdateUserPassword;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;

class PasswordControllerTest extends OrchestraTestCase
{
    use RefreshDatabase;

    public function test_passwords_can_be_updated()
    {
        $user = UserFactory::new()->create();

        $this->mock(UpdatesUserPasswords::class)
                    ->shouldReceive('update')
                    ->once();

        $response = $this->withoutExceptionHandling()->actingAs($user)->putJson('/user/password', [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertStatus(200);
    }

    public function test_passwords_cannot_be_updated_without_current_password()
    {
        $user = UserFactory::new()->create();

        try {
            (new UpdateUserPassword())->update($user, [
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);
        } catch (ValidationException $e) {
            $this->assertTrue(in_array(
                'The current password field is required.',
                $e->errors()['current_password']
            ));
        }
    }

    public function test_passwords_cannot_be_updated_without_current_password_confirmation()
    {
        $user = UserFactory::new()->create();

        try {
            (new UpdateUserPassword())->update($user, [
                'current_password' => 'invalid-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);
        } catch (ValidationException $e) {
            $this->assertTrue(in_array(
                'The provided password does not match your current password.',
                $e->errors()['current_password']
            ));
        }
    }
}
