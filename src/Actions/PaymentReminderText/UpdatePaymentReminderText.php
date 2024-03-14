<?php

namespace FluxErp\Actions\PaymentReminderText;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PaymentReminderText;
use FluxErp\Rulesets\PaymentReminderText\UpdatePaymentReminderTextRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdatePaymentReminderText extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdatePaymentReminderTextRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [PaymentReminderText::class];
    }

    public function performAction(): Model
    {
        $paymentReminderText = app(PaymentReminderText::class)->query()
            ->whereKey($this->data['id'])
            ->first();

        $paymentReminderText->fill($this->data);
        $paymentReminderText->save();

        return $paymentReminderText->withoutRelations()->fresh();
    }
}
