<?php

namespace Laravel\Fortify\Http\Requests;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Laravel\Fortify\Contracts\FailedTwoFactorLoginResponse;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;

class TwoFactorEnforcedSetupRequest extends FormRequest
{
    /**
     * The user setting up two factor authentication.
     *
     * @var mixed
     */
    protected $setupUser;

    /**
     * Indicates if the user wished to be remembered after login.
     *
     * @var bool
     */
    protected $remember;

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
            'code' => 'nullable|string',
        ];
    }

    /**
     * Determine if there is a user in the current session.
     *
     * @return bool
     */
    public function hasSetupUser()
    {
        if ($this->user) {
            return true;
        }

        $model = app(StatefulGuard::class)->getProvider()->getModel();

        return $this->session()->has('login.id') &&
            $model::find($this->session()->get('login.id'));
    }

    /**
     * Get the user that is setting up two factor authentication.
     *
     * @return mixed
     */
    public function setupUser()
    {
        if ($this->setupUser) {
            return $this->setupUser;
        }

        $model = app(StatefulGuard::class)->getProvider()->getModel();

        if (! $this->session()->has('login.id') ||
            ! $user = $model::find($this->session()->get('login.id'))) {
            throw new HttpResponseException(
                app(FailedTwoFactorLoginResponse::class)->toResponse($this)
            );
        }

        return $this->setupUser = $user;
    }

    /**
     * Determine if the user wanted to be remembered after login.
     *
     * @return bool
     */
    public function remember()
    {
        if (! $this->remember) {
            $this->remember = $this->session()->pull('login.remember', false);
        }

        return $this->remember;
    }
}
