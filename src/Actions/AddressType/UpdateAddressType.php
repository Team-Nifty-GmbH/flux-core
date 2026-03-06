<?php

namespace FluxErp\Actions\AddressType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AddressType;
use FluxErp\Rulesets\AddressType\UpdateAddressTypeRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class UpdateAddressType extends FluxAction
{
    public static function models(): array
    {
        return [AddressType::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateAddressTypeRuleset::class;
    }

    public function performAction(): Model
    {
        $tenants = Arr::pull($this->data, 'tenants');

        $addressType = resolve_static(AddressType::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $addressType->fill($this->data);
        $addressType->save();

        if (! is_null($tenants)) {
            $addressType->tenants()->sync($tenants);
        }

        return $addressType->withoutRelations()->fresh();
    }
}
