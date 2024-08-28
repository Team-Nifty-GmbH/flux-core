<?php

namespace FluxErp\Rulesets\Discount;

use FluxErp\Models\Discount;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteDiscountRuleset extends FluxRuleset
{
    protected static ?string $model = Discount::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Discount::class]),
            ],
        ];
    }
}
