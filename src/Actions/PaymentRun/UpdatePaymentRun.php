<?php

namespace FluxErp\Actions\PaymentRun;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Order;
use FluxErp\Models\PaymentRun;
use FluxErp\Rulesets\PaymentRun\UpdatePaymentRunRuleset;
use FluxErp\States\Order\PaymentState\InPayment;
use FluxErp\States\Order\PaymentState\Open;
use FluxErp\States\PaymentRun\Discarded;
use FluxErp\States\PaymentRun\NotSuccessful;
use FluxErp\States\PaymentRun\Pending;
use FluxErp\States\PaymentRun\Successful;
use Illuminate\Database\Eloquent\Model;

class UpdatePaymentRun extends FluxAction
{
    public static function models(): array
    {
        return [PaymentRun::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdatePaymentRunRuleset::class;
    }

    public function performAction(): Model
    {
        $payment = resolve_static(PaymentRun::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $oldState = $payment->state?->getValue();

        $payment->fill($this->data);
        $payment->save();

        $newState = $payment->state?->getValue();

        if ($oldState !== $newState && $newState) {
            $this->propagateStateToOrders($payment, $newState);
        }

        return $payment->withoutRelations()->fresh();
    }

    protected function propagateStateToOrders(PaymentRun $paymentRun, string $newState): void
    {
        $targetState = match (true) {
            in_array($newState, [Pending::$name, Successful::$name]) => InPayment::class,
            in_array($newState, [NotSuccessful::$name, Discarded::$name]) => Open::class,
            default => null,
        };

        if (! $targetState) {
            return;
        }

        $paymentRun->orders()
            ->select(['orders.id', 'orders.payment_state'])
            ->each(function (Order $order) use ($targetState): void {
                if ($order->payment_state->canTransitionTo($targetState)) {
                    $order->payment_state->transitionTo($targetState);
                }
            });
    }
}
