<?php

namespace FluxErp\Actions\PaymentReminder;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PaymentReminder;
use FluxErp\Rulesets\PaymentReminder\DeletePaymentReminderRuleset;

class DeletePaymentReminder extends FluxAction
{
    public static function models(): array
    {
        return [PaymentReminder::class];
    }

    protected function getRulesets(): string|array
    {
        return DeletePaymentReminderRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(PaymentReminder::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
