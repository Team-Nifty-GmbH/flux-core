<?php

namespace FluxErp\Rulesets\Warehouse;

use FluxErp\Models\Warehouse;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteWarehouseRuleset extends FluxRuleset
{
    protected static ?string $model = Warehouse::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Warehouse::class),
            ],
        ];
    }
}
