<?php

namespace FluxErp\Actions\PaymentRun;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Order;
use FluxErp\Models\PaymentRun;
use FluxErp\Rulesets\PaymentRun\CreatePaymentRunRuleset;
use FluxErp\States\Order\PaymentState\InOpenPaymentRun;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class CreatePaymentRun extends FluxAction
{
    public static function models(): array
    {
        return [PaymentRun::class];
    }

    protected function getRulesets(): string|array
    {
        return CreatePaymentRunRuleset::class;
    }

    public function performAction(): Model
    {
        $orders = Arr::pull($this->data, 'orders');

        $payment = app(PaymentRun::class, ['attributes' => $this->data]);
        $payment->save();

        $payment->orders()->attach($orders);

        $orderIds = array_column($orders, 'order_id');

        resolve_static(Order::class, 'query')
            ->whereIntegerInRaw('id', $orderIds)
            ->each(function (Order $order): void {
                if ($order->payment_state->canTransitionTo(InOpenPaymentRun::class)) {
                    $order->payment_state->transitionTo(InOpenPaymentRun::class);
                }
            });

        return $payment->fresh();
    }
}
