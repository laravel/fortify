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
     * Gets the "name" the user registered with.
     *
     * @return string|null
     */
    public function name();

    /**
     * Gets the "username" the user registered with.
     *
     * @return string
     */
    public function username();

    /**
     * Gets the "password" the user registered with.
     *
     * @return string
     */
    public function password();
}
