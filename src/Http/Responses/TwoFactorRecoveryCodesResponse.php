<?php

namespace Laravel\Fortify\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\TwoFactorRecoveryCodesResponse as TwoFactorRecoveryCodesResponseContract;

class TwoFactorRecoveryCodesResponse implements TwoFactorRecoveryCodesResponseContract
{
    /**
     * The user's two-factor recovery codes.
     *
     * @var non-empty-list<string>
     */
    protected $recoveryCodes;

    /**
     * Create a new response instance.
     *
     * @param  non-empty-list<string>  $recoveryCodes
     * @return void
     */
    public function __construct(array $recoveryCodes)
    {
        $this->recoveryCodes = $recoveryCodes;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        return new JsonResponse($this->recoveryCodes);
    }
}
