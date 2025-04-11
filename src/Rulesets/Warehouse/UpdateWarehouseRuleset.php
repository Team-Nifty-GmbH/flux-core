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
                app(ModelExists::class, ['model' => Warehouse::class]),
            ],
            'address_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Address::class]),
            ],
            'name' => 'sometimes|required|string|max:255',
            'is_default' => 'boolean',
        ];
    }
}
