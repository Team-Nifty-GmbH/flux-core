<?php

namespace FluxErp\Actions\PaymentReminder;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PaymentReminder;
use FluxErp\Rulesets\PaymentReminder\DeletePaymentReminderRuleset;

class DeletePaymentReminder extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeletePaymentReminderRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [PaymentReminder::class];
    }

    public function performAction(): ?bool
    {
        return resolve_static(PaymentReminder::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
