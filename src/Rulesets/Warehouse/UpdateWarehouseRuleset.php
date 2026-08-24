<?php

namespace FluxErp\Rulesets\Warehouse;

use FluxErp\Enums\StockRemovalStrategyEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Warehouse;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

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
                app(ModelExists::class, ['model' => Address::class, 'subject' => Warehouse::class]),
            ],
            'name' => 'sometimes|required|string|max:255',
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
