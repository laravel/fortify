<?php

namespace Laravel\Fortify\Http\Responses;

use Laravel\Fortify\Contracts\ConfirmPasswordViewResponse;
use Laravel\Fortify\Contracts\LoginViewResponse;
use Laravel\Fortify\Contracts\RegisterViewResponse;
use Laravel\Fortify\Contracts\RequestPasswordResetLinkViewResponse;
use Laravel\Fortify\Contracts\ResetPasswordViewResponse;
use Laravel\Fortify\Contracts\TwoFactorChallengeViewResponse;
use Laravel\Fortify\Contracts\VerifyEmailViewResponse;
use Illuminate\View\View;

class SimpleViewResponse implements
    LoginViewResponse,
    ResetPasswordViewResponse,
    RegisterViewResponse,
    RequestPasswordResetLinkViewResponse,
    TwoFactorChallengeViewResponse,
    VerifyEmailViewResponse,
    ConfirmPasswordViewResponse
{
    /**
     * The name of the view or the callable used to generate the view.
     *
     * @var callable|string
     */
    protected $view;

    /**
     * Create a new response instance.
     *
     * @param  callable|string  $view
     * @return void
     */
    public function __construct($view)
    {
        $this->view = $view;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        $response = call_user_func($this->view, $request);

        if (
            ! $response instanceof View
            && method_exists($response, 'toResponse')
        ) {
            return $response->toResponse($request);
        }

        return $response;
    }
}
