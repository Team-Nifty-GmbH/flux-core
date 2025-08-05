<?php

namespace FluxErp\Rulesets\Warehouse;

use FluxErp\Models\Warehouse;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteWarehouseRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = Warehouse::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Warehouse::class]),
            ],
        ];
    }
}
