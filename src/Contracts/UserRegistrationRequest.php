<?php

namespace Laravel\Fortify\Contracts;

interface UserRegistrationRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize();

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules();

    /**
     * Get the email address the user registered with.
     *
     * @return string
     */
    public function email();

    /**
     * Get the password the user registered with.
     *
     * @return string
     */
    public function password();
}
