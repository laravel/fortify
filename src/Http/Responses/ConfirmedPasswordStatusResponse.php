<?php

namespace Laravel\Fortify\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\ConfirmedPasswordStatusResponse as ConfirmedPasswordStatusResponseContract;

class ConfirmedPasswordStatusResponse implements ConfirmedPasswordStatusResponseContract
{
    /**
     * Whether the user's password has been confirmed.
     *
     * @var bool
     */
    protected $confirmed;

    /**
     * Timestamp of the last password confirmation.
     *
     * @var int
     */
    protected $lastConfirmed;

    /**
     * Create a new response instance.
     *
     * @param  bool  $confirmed
     * @param  int  $lastConfirmed
     * @return void
     */
    public function __construct(bool $confirmed, int $lastConfirmed)
    {
        $this->confirmed = $confirmed;
        $this->lastConfirmed = $lastConfirmed;
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
            'confirmed' => $this->confirmed,
        ], headers: array_filter([
            'X-Retry-After' => $this->confirmed ? $this->lastConfirmed : null,
        ]));
    }
}
