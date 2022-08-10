<?php

namespace App\Http\Requests;

use App\Actions\Fortify\PasswordValidationRules;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UserRegistrationRequest;

class RegisterUserRequest extends FormRequest implements UserRegistrationRequest
{
    use PasswordValidationRules;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ];
    }

    /**
     * Gets the name the user registered with.
     *
     * @return string
     */
    public function name()
    {
        return $this->get('name');
    }

    /**
     * Gets the email address the user registered with.
     *
     * @return string
     */
    public function email()
    {
        return $this->get('email');
    }

    /**
     * Gets the password the user registered with.
     *
     * @return string
     */
    public function password()
    {
        return $this->get('password');
    }
}
