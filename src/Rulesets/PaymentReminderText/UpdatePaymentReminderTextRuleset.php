<?php

namespace FluxErp\Rulesets\PaymentReminderText;

use FluxErp\Models\EmailTemplate;
use FluxErp\Models\PaymentReminder;
use FluxErp\Models\PaymentReminderText;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Sole;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Database\Eloquent\Builder;

class UpdatePaymentReminderTextRuleset extends FluxRuleset
{
    protected static ?string $model = PaymentReminderText::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => PaymentReminderText::class]),
            ],
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
            'reminder_body' => 'sometimes|required|string',
            'reminder_level' => [
                'required',
                'integer',
                'min:1',
                app(Sole::class, ['model' => PaymentReminderText::class]),
            ],
        ];
    }
}
