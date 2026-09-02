<?php

namespace FluxErp\Rulesets\Warehouse;

use FluxErp\Enums\StockRemovalStrategyEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Warehouse;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class CreateWarehouseRuleset extends FluxRuleset
{
    protected static ?string $model = Warehouse::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:warehouses,uuid',
            'address_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Address::class]),
            ],
            'name' => 'required|string|max:255',
            'is_default' => 'boolean',
            'requires_bin_location' => 'boolean',
            'stock_removal_strategy_enum' => [
                'sometimes',
                'required',
                Rule::enum(StockRemovalStrategyEnum::class),
            ],
        ];
    }
}
