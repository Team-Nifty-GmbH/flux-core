<?php

namespace FluxErp\Rulesets\DiscountGroup;

use FluxErp\Models\DiscountGroup;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteDiscountGroupRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = DiscountGroup::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => DiscountGroup::class]),
            ],
        ];
    }
}
