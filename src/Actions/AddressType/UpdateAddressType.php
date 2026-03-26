<?php

namespace FluxErp\Actions\AddressType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AddressType;
use FluxErp\Rulesets\AddressType\UpdateAddressTypeRuleset;
use Illuminate\Database\Eloquent\Model;

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
        $addressType = resolve_static(AddressType::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $addressType->fill($this->data);
        $addressType->save();

        return $addressType->withoutRelations()->fresh();
    }
}
