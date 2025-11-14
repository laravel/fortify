<?php

namespace Laravel\Fortify\Actions;

use Laravel\Fortify\CredentialsValidator;

class AttemptToAuthenticate
{
    /**
     * The credentials validator instance.
     *
     * @var \Laravel\Fortify\CredentialsValidator
     */
    protected $validator;

    /**
     * Create a new controller instance.
     *
     * @param  \Laravel\Fortify\CredentialsValidator  $validator
     * @return void
     */
    public function __construct(CredentialsValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  callable  $next
     * @return mixed
     */
    public function handle($request, $next)
    {
        $this->validator->getGuard()->login($this->validator->validateCredentials($request), $request->boolean('remember'));
        
        return $next($request);
    }
}
