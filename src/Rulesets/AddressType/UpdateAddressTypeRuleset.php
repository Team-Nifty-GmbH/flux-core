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
                app(ModelExists::class, ['model' => AddressType::class]),
            ],
            'address_type_code' => [
                'string',
                'max:255',
                'nullable',
                app(UniqueInFieldDependence::class, ['model' => AddressType::class, 'dependingField' => 'tenant_id']),
            ],
            'name' => 'sometimes|required|string|max:255',
            'is_locked' => 'boolean',
            'is_unique' => 'boolean',
        ];
    }
}
