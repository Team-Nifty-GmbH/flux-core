<?php

namespace FluxErp\Rulesets\PaymentReminder;

use FluxErp\Models\PaymentReminder;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeletePaymentReminderRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = PaymentReminder::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => PaymentReminder::class]),
            ],
        ];
    }
}
