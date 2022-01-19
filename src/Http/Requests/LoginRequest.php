<?php

namespace Laravel\Fortify\Http\Requests;

use App\Actions\Fortify\LoginValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    use LoginValidationRules;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return $this->loginRules();
    }
}
