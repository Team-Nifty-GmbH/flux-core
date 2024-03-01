<?php

namespace FluxErp\Rulesets\OrderPosition;

use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class DiscountRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'discounts' => 'array',
            'discounts.*.sort_number' => 'required|integer|min:0',
            'discounts.*.is_percentage' => 'required|boolean',
            'discounts.*.discount' => [
                'required',
                new Numeric(),
            ],
        ];
    }
}
