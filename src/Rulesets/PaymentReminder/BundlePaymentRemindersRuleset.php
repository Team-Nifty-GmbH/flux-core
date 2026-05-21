<?php

namespace FluxErp\Rulesets\PaymentReminder;

use FluxErp\Models\Order;
use FluxErp\Models\PaymentReminder;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class BundlePaymentRemindersRuleset extends FluxRuleset
{
    protected static ?string $model = PaymentReminder::class;

    public function rules(): array
    {
        return [
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Order::class])->wherePaymentReminderEligible(),
            ],
        ];
    }
}
