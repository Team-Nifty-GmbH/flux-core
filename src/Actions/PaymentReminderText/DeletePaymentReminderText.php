<?php

namespace FluxErp\Actions\PaymentReminderText;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PaymentReminderText;
use FluxErp\Rulesets\PaymentReminderText\DeletePaymentReminderTextRuleset;

class DeletePaymentReminderText extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeletePaymentReminderTextRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [PaymentReminderText::class];
    }

    public function performAction(): ?bool
    {
        return app(PaymentReminderText::class)->query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
