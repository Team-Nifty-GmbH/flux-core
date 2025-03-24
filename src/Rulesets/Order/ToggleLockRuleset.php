<?php

namespace FluxErp\Rulesets\Order;

use FluxErp\Models\Order;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class ToggleLockRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = Order::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Order::class]),
            ],
            'is_locked' => 'boolean',
        ];
    }
}
