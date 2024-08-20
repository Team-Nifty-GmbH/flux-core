<?php

namespace FluxErp\Rulesets\DiscountGroup;

use FluxErp\Models\Discount;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DiscountRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'discounts' => 'array',
            'discounts.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Discount::class]),
            ],
        ];
    }
}
