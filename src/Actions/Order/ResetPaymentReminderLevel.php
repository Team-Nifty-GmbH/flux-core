<?php

namespace FluxErp\Actions\Order;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Order;
use FluxErp\Rulesets\Order\ResetPaymentReminderLevelRuleset;
use Illuminate\Database\Eloquent\Model;

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

    public function performAction(): Model
    {
        $order = resolve_static(Order::class, 'query')
            ->whereKey($this->getData('id'))
            ->first(['id', 'payment_reminder_current_level']);

        $order->payment_reminder_current_level = $this->getData('payment_reminder_current_level');

        $order->save();

        return $order->withoutRelations()->fresh();
    }
}
