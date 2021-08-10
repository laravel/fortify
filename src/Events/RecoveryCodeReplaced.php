<?php

namespace Laravel\Fortify\Events;

use Illuminate\Queue\SerializesModels;

class RecoveryCodeReplaced
{
    use SerializesModels;

    /**
     * The authenticated user.
     *
     * @var \Illuminate\Contracts\Auth\Authenticatable
     */
    public $user;

    /**
     * The recovery code.
     *
     * @var string
     */
    public $code;

    /**
     * Create a new event instance.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $code
     * @return void
     */
    public function __construct($user, $code)
    {
        $this->user = $user;
        $this->code = $code;
    }
}
