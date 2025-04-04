<?php

namespace FluxErp\Rulesets\Discount;

use FluxErp\Models\Discount;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateDiscountRuleset extends FluxRuleset
{
    protected static ?string $model = Discount::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:discounts,uuid',
            'model_type' => [
                'required_with:model_id',
                'string',
                'max:255',
                app(MorphClassExists::class),
            ],
            'model_id' => [
                'required_with:model_type',
                'integer',
                app(MorphExists::class),
            ],
            'name' => 'nullable|string|max:255',
            'discount' => 'required|numeric',
            'from' => 'nullable|date_format:Y-m-d H:i:s',
            'till' => 'nullable|date_format:Y-m-d H:i:s',
            'order_column' => 'nullable|integer|min:1',
            'is_percentage' => 'required|boolean',
        ];
    }
}
