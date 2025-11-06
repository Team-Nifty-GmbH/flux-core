<?php

namespace FluxErp\Actions\Order;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\PaymentReminder\DeletePaymentReminder;
use FluxErp\Models\Order;
use FluxErp\Models\PaymentReminder;
use FluxErp\Rulesets\Order\ResetPaymentReminderLevelRuleset;
use Illuminate\Validation\ValidationException;

class ResetPaymentReminderLevel extends FluxAction
{
    public static function models(): array
    {
        return [Order::class];
    }

    protected function getRulesets(): string|array
    {
        return ResetPaymentReminderLevelRuleset::class;
    }

    public function performAction(): Order
    {
        $order = resolve_static(Order::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail(['id', 'payment_reminder_current_level']);

        $order->payment_reminder_current_level = $this->getData('payment_reminder_current_level');
        $order->save();

        $order->paymentReminders()
            ->where('reminder_level', '>=', $order->payment_reminder_current_level)
            ->get()
            ->each(function (PaymentReminder $paymentReminder): void {
                DeletePaymentReminder::make([
                    'id' => $paymentReminder->getKey(),
                ])
                    ->validate()
                    ->execute();
            });

        return $order->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $currentLevel = resolve_static(Order::class, 'query')
            ->whereKey($this->getData('id'))
            ->value('payment_reminder_current_level');

        if ($this->getData('payment_reminder_current_level') > $currentLevel) {
            throw ValidationException::withMessages([
                'payment_reminder_current_level' => ['New level must be smaller than current level'],
            ])
                ->errorBag('resetPaymentReminderLevel');
        }
    }
}
