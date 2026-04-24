<?php

namespace FluxErp\Actions\PaymentRun;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Order;
use FluxErp\Models\PaymentRun;
use FluxErp\Rulesets\PaymentRun\DeletePaymentRunRuleset;
use FluxErp\States\Order\PaymentState\Open;

class DeletePaymentRun extends FluxAction
{
    public static function models(): array
    {
        return [PaymentRun::class];
    }

    protected function getRulesets(): string|array
    {
        return DeletePaymentRunRuleset::class;
    }

    public function performAction(): ?bool
    {
        $paymentRun = resolve_static(PaymentRun::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $paymentRun->orders()
            ->select(['orders.id', 'orders.payment_state'])
            ->each(function (Order $order): void {
                if ($order->payment_state->canTransitionTo(Open::class)) {
                    $order->payment_state->transitionTo(Open::class);
                }
            });

        return $paymentRun->delete();
    }
}
