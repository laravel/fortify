<?php

namespace Laravel\Fortify\Events;

use Illuminate\Foundation\Events\Dispatchable;

class PasswordUpdatedViaController
{
    use Dispatchable;

    /**
     * The user instance.
     *
     * @var \Illuminate\Contracts\Auth\Authenticatable
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }
}
