<?php

namespace FluxErp\Actions\PaymentReminder;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Order\UpdateLockedOrder;
use FluxErp\Models\PaymentReminder;
use FluxErp\Rulesets\PaymentReminder\MarkPaymentReminderSentRuleset;

class MarkPaymentReminderSent extends FluxAction
{
    public static function models(): array
    {
        return [PaymentReminder::class];
    }

    protected function getRulesets(): string|array
    {
        return MarkPaymentReminderSentRuleset::class;
    }

    public function performAction(): PaymentReminder
    {
        $paymentReminder = resolve_static(PaymentReminder::class, 'query')
            ->whereKey($this->getData('id'))
            ->with('order:id,payment_reminder_days_1,payment_reminder_days_2,payment_reminder_days_3')
            ->firstOrFail();

        $order = $paymentReminder->order;
        $nextDateDays = $order->{'payment_reminder_days_' . ($paymentReminder->reminder_level + 1)}
            ?? $order->payment_reminder_days_3
            ?? 0;

        UpdateLockedOrder::make([
            'id' => $order->getKey(),
            'payment_reminder_current_level' => $paymentReminder->reminder_level,
            'payment_reminder_next_date' => $paymentReminder->created_at
                ->addDays($nextDateDays)
                ->toDateString(),
        ])
            ->validate()
            ->execute();

        return $paymentReminder->fresh();
    }
}
