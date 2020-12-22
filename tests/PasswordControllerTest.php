<?php

namespace Laravel\Fortify\Tests;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;
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
	
	public function test_password_update_can_fail_without_current_password()
	{
        $user = Mockery::mock(Authenticatable::class);

        $response = $this->withoutExceptionHandling()->actingAs($user)->putJson('/user/password', []);	
		$response->assertSessionHasErrorsIn('updatePassword',['current_password']);

	}
}
