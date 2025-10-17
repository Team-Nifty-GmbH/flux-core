<?php

namespace FluxErp\Rulesets\Order;

use FluxErp\Models\Order;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class ResetPaymentReminderLevelRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Order::class]),
            ],
            'payment_reminder_current_level' => [
                'required',
                'integer',
                'min:0',
            ],
        ];
    }
}
