<?php

namespace FluxErp\Rulesets\Order;

use FluxErp\Models\Order;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class ResetPaymentReminderLevelRuleset extends FluxRuleset
{
    protected static ?string $model = Order::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Order::class]),
            ],
            'payment_reminder_current_level' => [
                'nullable',
                'integer',
                'min:0',
                'max:3',
            ],
        ];
    }
}
