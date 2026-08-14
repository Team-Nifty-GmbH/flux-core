<?php

namespace FluxErp\Actions\PaymentRun;

use FluxErp\Actions\FluxAction;
use FluxErp\Enums\PaymentRunTypeEnum;
use FluxErp\Jobs\Accounting\SendPaymentAdviceJob;
use FluxErp\Models\Order;
use FluxErp\Models\PaymentRun;
use FluxErp\Rulesets\PaymentRun\UpdatePaymentRunRuleset;
use FluxErp\Settings\AccountingSettings;
use FluxErp\States\Order\PaymentState\InPayment;
use FluxErp\States\Order\PaymentState\Open;
use FluxErp\States\PaymentRun\Discarded;
use FluxErp\States\PaymentRun\NotSuccessful;
use FluxErp\States\PaymentRun\Pending;
use FluxErp\States\PaymentRun\Successful;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

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
            ->whereKey($this->getData('id'))
            ->first();

        $oldState = $payment->state?->getValue();

        $payment->fill($this->data);
        $payment->save();

        $newState = $payment->state?->getValue();

        if ($oldState !== $newState && $newState) {
            $this->propagateStateToOrders($payment, $newState);
            $this->sendPaymentAdvices($payment, $oldState, $newState);
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

        $settledOrderIds = $paymentRun->settledPositionOrderIds();

        $paymentRun->orders()
            ->select(['orders.id', 'orders.payment_state'])
            ->whereIntegerNotInRaw('orders.id', $settledOrderIds)
            ->each(function (Order $order) use ($targetState): void {
                if ($order->payment_state->canTransitionTo($targetState)) {
                    $order->payment_state->transitionTo($targetState);
                }
            });

        resolve_static(Order::class, 'query')
            ->whereIntegerInRaw('id', $settledOrderIds)
            ->each(fn (Order $order) => $order->calculatePaymentState()->save());
    }

    protected function sendPaymentAdvices(PaymentRun $paymentRun, ?string $oldState, string $newState): void
    {
        if (! app(AccountingSettings::class)->auto_send_payment_advice) {
            return;
        }

        if ($paymentRun->payment_run_type_enum !== PaymentRunTypeEnum::MoneyTransfer) {
            return;
        }

        $enteredPayment = in_array($newState, [Pending::$name, Successful::$name], true);
        $wasAlreadyInPayment = in_array($oldState, [Pending::$name, Successful::$name], true);

        if (! $enteredPayment || $wasAlreadyInPayment) {
            return;
        }

        $jobs = $paymentRun->positions()
            ->where('amount', '!=', 0)
            ->pluck('id')
            ->map(fn (int $positionId) => app(SendPaymentAdviceJob::class, ['positionId' => $positionId]))
            ->all();

        if (! $jobs) {
            return;
        }

        DB::afterCommit(fn () => Bus::monitoredBatch($jobs)
            ->name(__('Payment Advices'))
            ->allowFailures()
            ->dispatch());
    }
}
