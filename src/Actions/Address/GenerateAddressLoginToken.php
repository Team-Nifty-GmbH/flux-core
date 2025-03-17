<?php

namespace FluxErp\Actions\Address;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Address;

class GenerateAddressLoginToken extends FluxAction
{
    public static function models(): array
    {
        return [Address::class];
    }

    public function performAction(): array
    {
        return resolve_static(Address::class, 'query')
            ->whereKey($this->getData('id'))
            ->first()
            ->createLoginToken();
    }
}
