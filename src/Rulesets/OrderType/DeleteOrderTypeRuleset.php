<?php

namespace FluxErp\Rulesets\OrderType;

use FluxErp\Models\OrderType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteOrderTypeRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = OrderType::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => OrderType::class]),
            ],
        ];
    }
}
