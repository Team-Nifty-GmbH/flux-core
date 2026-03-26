<?php

namespace FluxErp\Rulesets\AddressType;

use FluxErp\Models\AddressType;
use FluxErp\Models\Tenant;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class CreateAddressTypeRuleset extends FluxRuleset
{
    protected static ?string $model = AddressType::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:address_types,uuid',
            'address_type_code' => [
                'string',
                'nullable',
                'max:255',
                Rule::unique('address_types', 'address_type_code'),
            ],
            'name' => 'required|string|max:255',
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
