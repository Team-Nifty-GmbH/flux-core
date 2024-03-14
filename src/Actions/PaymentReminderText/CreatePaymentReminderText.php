<?php

namespace FluxErp\Actions\PaymentReminderText;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PaymentReminderText;
use FluxErp\Rulesets\PaymentReminderText\CreatePaymentReminderTextRuleset;

class CreatePaymentReminderText extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreatePaymentReminderTextRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [PaymentReminderText::class];
    }

    public function performAction(): mixed
    {
        $paymentReminderText = app(PaymentReminderText::class, ['attributes' => $this->data]);
        $paymentReminderText->save();

        return $paymentReminderText->fresh();
    }
}
