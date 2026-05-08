<?php

namespace FluxErp\Actions\Passkey;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Spatie\LaravelPasskeys\Actions\GeneratePasskeyAuthenticationOptionsAction as BaseGeneratePasskeyAuthenticationOptionsAction;
use Spatie\LaravelPasskeys\Support\Config;
use Spatie\LaravelPasskeys\Support\Serializer;
use Webauthn\PublicKeyCredentialRequestOptions;

class GeneratePasskeyAuthenticationOptionsAction extends BaseGeneratePasskeyAuthenticationOptionsAction
{
    public function execute(): string
    {
        $options = new PublicKeyCredentialRequestOptions(
            challenge: Str::random(),
            rpId: Config::getRelyingPartyId(),
            allowCredentials: [],
        );

        $options = Serializer::make()->toJson($options);

        // put() instead of the parent's flash(): flash data is aged out
        // after one subsequent request, so any intermediate Livewire poll
        // or asset load consumes it before the authenticate POST arrives.
        // The controller pulls() the value to preserve single-use semantics.
        Session::put('passkey-authentication-options', $options);

        return $options;
    }
}
