<?php

namespace FluxErp\Rulesets\PaymentReminderText;

use FluxErp\Models\PaymentReminderText;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeletePaymentReminderTextRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = PaymentReminderText::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => PaymentReminderText::class]),
            ],
        ];
    }
}
