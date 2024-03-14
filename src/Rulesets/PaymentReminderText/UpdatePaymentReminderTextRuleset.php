<?php

namespace FluxErp\Rulesets\PaymentReminderText;

use FluxErp\Models\PaymentReminder;
use FluxErp\Models\PaymentReminderText;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Sole;
use FluxErp\Rulesets\FluxRuleset;

class UpdatePaymentReminderTextRuleset extends FluxRuleset
{
    protected static ?string $model = PaymentReminderText::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(PaymentReminder::class),
            ],
            'mail_to' => 'nullable|string|email',
            'mail_cc' => 'nullable|string|email',
            'mail_subject' => 'nullable|string',
            'mail_body' => 'nullable|string',
            'reminder_subject' => 'nullable|string',
            'reminder_body' => 'string',
            'reminder_level' => [
                'required',
                'integer',
                'min:1',
                new Sole(PaymentReminderText::class),
            ],
        ];
    }
}
