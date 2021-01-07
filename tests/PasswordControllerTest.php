<?php

namespace Laravel\Fortify\Tests;

use App\Actions\Fortify\UpdateUserPassword;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;
use Mockery;

class PasswordControllerTest extends OrchestraTestCase
{
    public function test_passwords_can_be_updated()
    {
        $user = Mockery::mock(Authenticatable::class);

        $user->password = Hash::make('password');

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

    public function test_passwords_cannot_be_updated_without_password_confirmation()
    {
        $user = Mockery::mock(Authenticatable::class);
        $user->password = '';

        require_once __DIR__.'/../stubs/PasswordValidationRules.php';
        require_once __DIR__.'/../stubs/UpdateUserPassword.php';

        try {
            (new UpdateUserPassword())->update($user, [
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
