<?php

namespace FluxErp\Rulesets\AddressType;

use FluxErp\Models\AddressType;
use FluxErp\Models\Tenant;
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
            ],
            'name' => 'sometimes|required|string|max:255',
            'is_locked' => 'boolean',
            'is_unique' => 'boolean',

            'tenants' => 'array|nullable',
            'tenants.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Tenant::class]),
            ],
        ];
    }
}
