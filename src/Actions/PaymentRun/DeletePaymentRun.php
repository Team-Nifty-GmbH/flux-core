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

        $settledOrderIds = $paymentRun->settledPositionOrderIds();

        $paymentRun->orders()
            ->select(['orders.id', 'orders.payment_state'])
            ->whereIntegerNotInRaw('orders.id', $settledOrderIds)
            ->each(function (Order $order): void {
                if ($order->payment_state->canTransitionTo(Open::class)) {
                    $order->payment_state->transitionTo(Open::class);
                }
            });

        resolve_static(Order::class, 'query')
            ->whereIntegerInRaw('id', $settledOrderIds)
            ->each(fn (Order $order) => $order->calculatePaymentState()->save());

        return $paymentRun->delete();
    }
}
