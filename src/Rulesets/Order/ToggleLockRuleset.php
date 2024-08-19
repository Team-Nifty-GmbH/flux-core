<?php

namespace FluxErp\Rulesets\Order;

use FluxErp\Models\Order;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class ToggleLockRuleset extends FluxRuleset
{
    protected static ?string $model = Order::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Order::class),
            ],
            'is_locked' => 'boolean',
        ];
    }
}
