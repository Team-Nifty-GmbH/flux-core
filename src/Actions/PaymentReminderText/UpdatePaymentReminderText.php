<?php

namespace FluxErp\Actions\PaymentReminderText;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PaymentReminderText;
use FluxErp\Rulesets\PaymentReminderText\UpdatePaymentReminderTextRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdatePaymentReminderText extends FluxAction
{
    public static function models(): array
    {
        return [PaymentReminderText::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdatePaymentReminderTextRuleset::class;
    }

    public function performAction(): Model
    {
        $paymentReminderText = resolve_static(PaymentReminderText::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $paymentReminderText->fill($this->data);
        $paymentReminderText->save();

        return $paymentReminderText->withoutRelations()->fresh();
    }
}
