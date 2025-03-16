<?php

namespace FluxErp\Actions\Address;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Address;
use FluxErp\Rulesets\Address\DeleteAddressRuleset;

class DeleteAddress extends FluxAction
{
    public static function models(): array
    {
        return [Address::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteAddressRuleset::class;
    }

    public function performAction(): ?bool
    {
        $address = resolve_static(Address::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $address->addressTypes()->detach();
        $address->tokens()->delete();

        return $address->delete();
    }
}
