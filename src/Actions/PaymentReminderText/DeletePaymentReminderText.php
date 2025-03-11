<?php

namespace FluxErp\Actions\PaymentReminderText;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PaymentReminderText;
use FluxErp\Rulesets\PaymentReminderText\DeletePaymentReminderTextRuleset;

class DeletePaymentReminderText extends FluxAction
{
    public static function models(): array
    {
        return [PaymentReminderText::class];
    }

    protected function getRulesets(): string|array
    {
        return DeletePaymentReminderTextRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(PaymentReminderText::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
