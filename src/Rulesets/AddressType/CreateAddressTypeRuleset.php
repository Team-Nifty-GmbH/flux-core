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
            'tenant_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Tenant::class]),
            ],
            'address_type_code' => [
                'string',
                'nullable',
                'max:255',
                Rule::unique('address_types')->where('tenant_id', $data['tenant_id'] ?? null),
            ],
            'name' => 'required|string|max:255',
            'is_locked' => 'boolean',
            'is_unique' => 'boolean',
        ];
    }
}
