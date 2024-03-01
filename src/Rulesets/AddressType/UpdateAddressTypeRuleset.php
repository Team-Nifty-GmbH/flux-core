<?php

namespace FluxErp\Rulesets\AddressType;

use FluxErp\Models\AddressType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\UniqueInFieldDependence;
use FluxErp\Rulesets\FluxRuleset;

class UpdateAddressTypeRuleset extends FluxRuleset
{
    protected static ?string $model = AddressType::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(AddressType::class),
            ],
            'address_type_code' => [
                'string',
                'nullable',
                new UniqueInFieldDependence(AddressType::class, 'client_id'),
            ],
            'name' => 'sometimes|required|string',
            'is_locked' => 'boolean',
            'is_unique' => 'boolean',
        ];
    }
}
