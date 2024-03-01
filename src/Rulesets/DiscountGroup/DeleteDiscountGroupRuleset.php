<?php

namespace FluxErp\Rulesets\DiscountGroup;

use FluxErp\Models\DiscountGroup;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteDiscountGroupRuleset extends FluxRuleset
{
    protected static ?string $model = DiscountGroup::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(DiscountGroup::class),
            ],
        ];
    }
}
