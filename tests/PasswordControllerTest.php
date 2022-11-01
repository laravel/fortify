<?php

namespace Laravel\Fortify\Tests;

use App\Actions\Fortify\UpdateUserPassword;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;
use Mockery;

class PasswordControllerTest extends OrchestraTestCase
{
    public function test_passwords_can_be_updated()
    {
        $user = Mockery::mock(User::class);

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
        $user = Mockery::mock(User::class);

        require_once __DIR__.'/../stubs/PasswordValidationRules.php';
        require_once __DIR__.'/../stubs/UpdateUserPassword.php';

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
        $user = Mockery::mock(User::class);

        require_once __DIR__.'/../stubs/PasswordValidationRules.php';
        require_once __DIR__.'/../stubs/UpdateUserPassword.php';

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
