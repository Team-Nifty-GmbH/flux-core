<?php

namespace FluxErp\Rulesets\AddressType;

use FluxErp\Models\AddressType;
use FluxErp\Models\Client;
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
            'client_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Client::class]),
            ],
            'address_type_code' => [
                'string',
                'nullable',
                'max:255',
                Rule::unique('address_types')->where('client_id', $data['client_id'] ?? null),
            ],
            'name' => 'required|string|max:255',
            'is_locked' => 'boolean',
            'is_unique' => 'boolean',
        ];
    }
}
