<?php

namespace FluxErp\Actions\Passkey;

use Spatie\LaravelPasskeys\Actions\StorePasskeyAction as BaseStorePasskeyAction;
use Spatie\LaravelPasskeys\Models\Concerns\HasPasskeys;
use Spatie\LaravelPasskeys\Models\Passkey;

class StorePasskeyAction extends BaseStorePasskeyAction
{
    public function execute(
        HasPasskeys $authenticatable,
        string $passkeyJson,
        string $passkeyOptionsJson,
        string $hostName,
        array $additionalProperties = [],
    ): Passkey {
        $additionalProperties['authenticatable_type'] ??= $authenticatable->getMorphClass();

        return parent::execute(
            $authenticatable,
            $passkeyJson,
            $passkeyOptionsJson,
            $hostName,
            $additionalProperties,
        );
    }
}
