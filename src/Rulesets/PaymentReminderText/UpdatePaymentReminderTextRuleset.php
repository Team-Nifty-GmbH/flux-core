<?php

namespace FluxErp\Rulesets\PaymentReminderText;

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
                app(ModelExists::class, ['model' => PaymentReminderText::class]),
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
