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
            'orders' => 'required|array|min:1',
            'orders.*.id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Order::class])
                    ->wherePaymentReminderEligible(),
            ],
            'orders.*.recipient' => 'nullable|email',
        ];
    }
}
