<?php

namespace Laravel\Fortify\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\TwoFactorSecretKeyResponse as TwoFactorSecretKeyResponseContract;

class TwoFactorSecretKeyResponse implements TwoFactorSecretKeyResponseContract
{
    /**
     * The user's two-factor secret key.
     *
     * @var string
     */
    protected $secretKey;

    /**
     * Create a new response instance.
     *
     * @param  string  $secretKey
     * @return void
     */
    public function __construct(string $secretKey)
    {
        $this->secretKey = $secretKey;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        return new JsonResponse([
            'secretKey' => $this->secretKey,
        ]);
    }
}
