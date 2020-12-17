<?php

namespace Laravel\Fortify\Events;

use Illuminate\Foundation\Events\Dispatchable;

class RecoveryCodesGenerated
{
    use Dispatchable;

    /**
     * The user instance.
     *
     * @var \App\Models\User
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }
}
