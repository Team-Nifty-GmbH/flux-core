<?php

namespace FluxErp\Rulesets\Contact;

use FluxErp\Models\Discount;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class DiscountRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'discounts' => 'nullable|array',
            'discounts.*.id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Discount::class]),
            ],
            'discounts.*.sort_number' => 'required_without:id|integer|min:0',
            'discounts.*.is_percentage' => 'required_without:id|boolean',
            'discounts.*.discount' => [
                'required_without:id',
                app(Numeric::class),
            ],
        ];
    }
}
