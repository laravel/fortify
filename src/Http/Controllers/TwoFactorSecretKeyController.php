<?php

namespace Laravel\Fortify\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Crypt;

class TwoFactorSecretKeyController extends Controller
{
    /**
     * Get the current user's two factor authentication setup / secret key.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(Request $request)
    {
        if (is_null($request->user()->two_factor_secret)) {
            abort(404, 'Two factor authentication has not been enabled.');
        }

        return response()->json([
            'secretKey' => (Model::$encrypter ?? Crypt::getFacadeRoot())->decrypt($request->user()->two_factor_secret),
        ]);
    }
}
