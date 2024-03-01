<?php

namespace FluxErp\Rulesets\Warehouse;

use FluxErp\Models\Address;
use FluxErp\Models\Warehouse;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateWarehouseRuleset extends FluxRuleset
{
    protected static ?string $model = Warehouse::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Warehouse::class),
            ],
            'address_id' => [
                'integer',
                'nullable',
                new ModelExists(Address::class),
            ],
            'name' => 'sometimes|required|string',
            'is_default' => 'boolean',
        ];
    }
}
