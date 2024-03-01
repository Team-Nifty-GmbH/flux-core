<?php

namespace FluxErp\Rulesets\OrderType;

use FluxErp\Models\OrderType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteOrderTypeRuleset extends FluxRuleset
{
    protected static ?string $model = OrderType::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(OrderType::class),
            ],
        ];
    }
}
