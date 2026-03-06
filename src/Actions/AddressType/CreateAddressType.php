<?php

namespace FluxErp\Actions\AddressType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AddressType;
use FluxErp\Rulesets\AddressType\CreateAddressTypeRuleset;
use Illuminate\Support\Arr;

class CreateAddressType extends FluxAction
{
    public static function models(): array
    {
        return [AddressType::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateAddressTypeRuleset::class;
    }

    public function performAction(): AddressType
    {
        $tenants = Arr::pull($this->data, 'tenants');

        $addressType = app(AddressType::class, ['attributes' => $this->data]);
        $addressType->save();

        if ($tenants) {
            $addressType->tenants()->attach($tenants);
        }

        return $addressType->fresh();
    }
}
