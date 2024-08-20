<?php

namespace FluxErp\Rulesets\OrderPosition;

use FluxErp\Models\OrderPosition;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteOrderPositionRuleset extends FluxRuleset
{
    protected static ?string $model = OrderPosition::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => OrderPosition::class]),
            ],
        ];
    }
}
