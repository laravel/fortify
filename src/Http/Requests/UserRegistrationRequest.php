<?php

namespace Laravel\Fortify\Http\Requests;

use App\Actions\Fortify\PasswordValidationRules;
use App\Actions\Fortify\UsernameValidationRules;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Laravel\Fortify\Contracts\UserRegistrationRequest as UserRegistrationRequestContract;
use Laravel\Fortify\Fortify;

class UserRegistrationRequest extends FormRequest implements UserRegistrationRequestContract
{
    use PasswordValidationRules;
    use UsernameValidationRules;

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
            Fortify::username() => $this->usernameRules(),
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
