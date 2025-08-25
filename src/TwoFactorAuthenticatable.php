<?php

namespace Laravel\Fortify;

use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Illuminate\Support\Facades\URL;

trait TwoFactorAuthenticatable {

    public function hasEnabledTwoFactorAuthentication() {

        if(Fortify::confirmsTwoFactorAuthentication()) {

            return ! is_null($this->two_factor_secret) &&
                   ! is_null($this->two_factor_confirmed_at);

        }

        return ! is_null($this->two_factor_secret);

    }


    public function recoveryCodes() {

        return json_decode(decrypt($this->two_factor_recovery_codes), true);

    }

    public function replaceRecoveryCode($code) {

        $this->forceFill([

            'two_factor_recovery_codes' => encrypt (str_replace (

                $code,
                RecoveryCode::generate(),
                decrypt($this->two_factor_recovery_codes)

            )),

        ])->save();

    }

    public function twoFactorQrCodeSvg() {

        $svg = (new Writer (

            new ImageRenderer (

                new RendererStyle (192, 0, null, null, Fill::uniformColor (new Rgb(255, 255, 255), new Rgb (45, 55, 72))),

                new SvgImageBackEnd

            )

        ))->writeString($this->twoFactorQrCodeUrl());

        return trim(substr($svg, strpos($svg, "\n") + 1));

    }


    public function twoFactorQrCodeUrl() {

        $url = parse_url(config('app.url'), PHP_URL_HOST);

        if(session("login") == "ramal") {

            $email = "{$this->accountcode}{$this->alias}@$url";

        } else {

            $email = $this->email;

        }

        return app(TwoFactorAuthenticationProvider::class)->qrCodeUrl (

            config('app.name'),
            $email,
            decrypt($this->two_factor_secret)

        );

    }

}