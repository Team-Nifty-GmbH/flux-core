<?php

namespace FluxErp\Rulesets\PaymentReminderText;

use FluxErp\Models\PaymentReminderText;
use FluxErp\Rules\Sole;
use FluxErp\Rulesets\FluxRuleset;

class CreatePaymentReminderTextRuleset extends FluxRuleset
{
    protected static ?string $model = PaymentReminderText::class;

    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:payment_reminders,uuid',
            'mail_to' => 'nullable|string|email',
            'mail_cc' => 'nullable|string|email',
            'mail_subject' => 'nullable|string',
            'mail_body' => 'nullable|string',
            'reminder_subject' => 'nullable|string',
            'reminder_body' => 'required|string',
            'reminder_level' => [
                'required',
                'integer',
                'min:1',
                'unique:payment_reminder_texts,reminder_level',
            ],
        ];
    }
}
