<?php

namespace FluxErp\Actions\PaymentReminder;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PaymentReminder;
use FluxErp\Rulesets\PaymentReminder\UpdatePaymentReminderRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdatePaymentReminder extends FluxAction
{
    public static function models(): array
    {
        return [PaymentReminder::class];
    }

    public static function name(): string
    {
        return resolve_static(CreatePaymentReminder::class, 'name');
    }

    protected function getRulesets(): string|array
    {
        return UpdatePaymentReminderRuleset::class;
    }

    public function performAction(): Model
    {
        $paymentReminder = resolve_static(PaymentReminder::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $paymentReminder->fill($this->data);
        $paymentReminder->save();

        return $paymentReminder->withoutRelations()->fresh();
    }
}
