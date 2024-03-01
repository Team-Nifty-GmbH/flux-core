<?php

namespace FluxErp\Rulesets\PriceList;

use FluxErp\Rulesets\FluxRuleset;

class DiscountRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'discount' => 'exclude_without:parent_id|exclude_if:parent_id,NULL|array',
            'discount.discount' => 'exclude_without:discount|present|numeric|nullable',
            'discount.is_percentage' => 'exclude_without:discount|required|boolean',
        ];
    }
}
