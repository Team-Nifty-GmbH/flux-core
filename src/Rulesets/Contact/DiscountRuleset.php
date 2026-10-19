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
            'discounts_pivot_sync_type' => [
                'nullable',
                'string',
                'in:sync,attach,detach,syncWithoutDetaching',
            ],
            'discounts' => 'nullable|array',
            'discounts.*.id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Discount::class]),
            ],
            'discounts.*.sort_number' => [
                'exclude_with:discounts.*.id',
                'required_without:discounts.*.id',
                'integer',
                'min:0',
            ],
            'discounts.*.is_percentage' => [
                'exclude_with:discounts.*.id',
                'required_without:discounts.*.id',
                'boolean',
            ],
            'discounts.*.discount' => [
                'exclude_with:discounts.*.id',
                'required_without:discounts.*.id',
                app(Numeric::class),
            ],
        ];
    }
}
