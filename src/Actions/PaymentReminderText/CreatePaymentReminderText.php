<?php

namespace FluxErp\Actions\PaymentReminderText;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PaymentReminderText;
use FluxErp\Rulesets\PaymentReminderText\CreatePaymentReminderTextRuleset;

class CreatePaymentReminderText extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return CreatePaymentReminderTextRuleset::class;
    }

    public static function models(): array
    {
        return [PaymentReminderText::class];
    }

    public function performAction(): PaymentReminderText
    {
        $paymentReminderText = app(PaymentReminderText::class, ['attributes' => $this->data]);
        $paymentReminderText->save();

        return $paymentReminderText->fresh();
    }
}
