<?php

namespace FluxErp\Rulesets\PaymentReminder;

use FluxErp\Models\Media;
use FluxErp\Models\Order;
use FluxErp\Models\PaymentReminder;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreatePaymentReminderRuleset extends FluxRuleset
{
    protected static ?string $model = PaymentReminder::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:payment_reminders,uuid',
            'order_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Order::class]),
            ],
            'media_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Media::class]),
            ],
            'reminder_level' => 'nullable|integer|min:1',
        ];
    }
}
