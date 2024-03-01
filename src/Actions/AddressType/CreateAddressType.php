<?php

namespace FluxErp\Actions\AddressType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AddressType;
use FluxErp\Rulesets\AddressType\CreateAddressTypeRuleset;
use Illuminate\Support\Facades\Validator;

class CreateAddressType extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateAddressTypeRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [AddressType::class];
    }

    public function performAction(): AddressType
    {
        $addressType = app(AddressType::class, ['attributes' => $this->data]);
        $addressType->save();

        return $addressType->fresh();
    }

    protected function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(app(AddressType::class));

        $this->data = $validator->validate();
    }
}
