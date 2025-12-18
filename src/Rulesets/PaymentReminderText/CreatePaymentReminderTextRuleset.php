<?php

namespace FluxErp\Rulesets\PaymentReminderText;

use FluxErp\Models\EmailTemplate;
use FluxErp\Models\PaymentReminder;
use FluxErp\Models\PaymentReminderText;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Database\Eloquent\Builder;

class CreatePaymentReminderTextRuleset extends FluxRuleset
{
    protected static ?string $model = PaymentReminderText::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:payment_reminders,uuid',
            'email_template_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => EmailTemplate::class])
                    ->where(function (Builder $query): void {
                        $query->whereNull('model_type')
                            ->orWhere('model_type', morph_alias(PaymentReminder::class));
                    }),
            ],
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
