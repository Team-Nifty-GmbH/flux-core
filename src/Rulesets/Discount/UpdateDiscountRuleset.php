<?php

namespace FluxErp\Rulesets\Discount;

use FluxErp\Models\Discount;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateDiscountRuleset extends FluxRuleset
{
    protected static ?string $model = Discount::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Discount::class),
            ],
            'discount' => 'required_with:is_percentage|numeric',
            'from' => 'nullable|date_format:Y-m-d H:i:s',
            'till' => 'nullable|date_format:Y-m-d H:i:s',
            'sort_number' => 'sometimes|integer|min:0',
            'is_percentage' => 'sometimes|boolean',
        ];
    }
}
