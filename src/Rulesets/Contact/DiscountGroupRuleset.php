<?php

namespace FluxErp\Rulesets\Contact;

use FluxErp\Models\DiscountGroup;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DiscountGroupRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'discount_groups' => 'array',
            'discount_groups.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => DiscountGroup::class]),
            ],
        ];
    }
}
