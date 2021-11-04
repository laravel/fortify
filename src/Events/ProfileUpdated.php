<?php

namespace Laravel\Fortify\Events;

use Illuminate\Queue\SerializesModels;

class ProfileUpdated
{
    use SerializesModels;

    /**
     * The user.
     *
     * @var \Illuminate\Contracts\Auth\Authenticatable
     */
    public $user;

    /**
     * The old email address, if changed.
     *
     * @var string
     */
    public $oldEmail;

    /**
     * Create a new event instance.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return void
     */
    public function __construct($user, $oldEmail = null)
    {
        $this->user = $user;
        $this->oldEmail = $oldEmail;
    }
}
