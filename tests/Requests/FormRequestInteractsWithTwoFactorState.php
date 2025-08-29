<?php

namespace Laravel\Fortify\Tests\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Laravel\Fortify\InteractsWithTwoFactorState;

class FormRequestInteractsWithTwoFactorState extends FormRequest
{
    use InteractsWithTwoFactorState;
}
