<?php

namespace FluxErp\Support\Matching;

use FluxErp\Actions\OrderTransaction\CreateOrderTransaction;
use FluxErp\Enums\PaymentRunTypeEnum;
use FluxErp\Models\PaymentRunPosition;
use FluxErp\Models\Transaction;
use FluxErp\States\Order\PaymentState\Paid;
use FluxErp\States\PaymentRun\Pending;
use FluxErp\States\PaymentRun\Successful;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PaymentRunMatcher
{
    protected const DAYS_BEFORE = 2;

    protected const DAYS_AFTER = 14;

    public function match(Transaction $transaction): bool
    {
        $positions = $this->byReference($transaction)
            ?? $this->byAmountAndIban($transaction)
            ?? $this->byCollectiveBooking($transaction);

        if (! $positions || $positions->isEmpty()) {
            return false;
        }

        if (! $this->sumsToTransactionAmount($positions, $transaction)) {
            return false;
        }

        $positions->each(fn (PaymentRunPosition $position) => $this->assign($position, $transaction));

        return true;
    }

    protected function byReference(Transaction $transaction): ?Collection
    {
        if (! $transaction->end_to_end_reference) {
            return null;
        }

        $position = $this->openPositions($transaction)
            ->where('end_to_end_id', $transaction->end_to_end_reference)
            ->first();

        return $position ? collect([$position]) : null;
    }

    protected function byAmountAndIban(Transaction $transaction): ?Collection
    {
        if (! $transaction->counterpart_iban) {
            return null;
        }

        $positions = $this->openPositions($transaction)
            ->where('iban', $transaction->counterpart_iban)
            ->whereRaw('ROUND(ABS(amount), 2) = ROUND(ABS(?), 2)', [$transaction->amount])
            ->get()
            ->filter(fn (PaymentRunPosition $position) => $this->isInWindow($position, $transaction));

        return $positions->count() === 1 ? $positions->values() : null;
    }

    protected function byCollectiveBooking(Transaction $transaction): ?Collection
    {
        $from = $transaction->booking_date->copy()->subDays(static::DAYS_AFTER)->toDateString();
        $to = $transaction->booking_date->copy()->addDays(static::DAYS_BEFORE)->toDateString();

        $runs = $this->openPositions($transaction)
            ->whereHas('paymentRun', fn (Builder $query) => $query
                ->where('is_single_booking', false)
                ->whereRaw('DATE(COALESCE(instructed_execution_date, created_at)) BETWEEN ? AND ?', [$from, $to])
            )
            ->with(['paymentRun', 'orders'])
            ->get()
            ->groupBy('payment_run_id');

        return $runs
            ->first(fn (Collection $positions) => $this->sumsToTransactionAmount($positions, $transaction))
            ?->values();
    }

    protected function openPositions(Transaction $transaction): Builder
    {
        return resolve_static(PaymentRunPosition::class, 'query')
            ->where('amount', '!=', 0)
            ->whereHas(
                'paymentRun',
                fn (Builder $query) => $query
                    ->whereIn('state', [Pending::$name, Successful::$name])
                    ->where(
                        'payment_run_type_enum',
                        bccomp((string) $transaction->amount, '0', 2) === -1
                            ? PaymentRunTypeEnum::MoneyTransfer
                            : PaymentRunTypeEnum::DirectDebit
                    )
            )
            ->whereDoesntHave(
                'orders.transactions',
                fn (Builder $query) => $query->whereKey($transaction->getKey())
            )
            ->whereDoesntHave(
                'orders',
                fn (Builder $query) => $query
                    ->where('order_payment_run.success', true)
                    ->orWhereState('payment_state', Paid::class)
            );
    }

    protected function isInWindow(PaymentRunPosition $position, Transaction $transaction): bool
    {
        $executionDate = $position->paymentRun->instructed_execution_date
            ?? $position->paymentRun->created_at;

        return $transaction->booking_date->betweenIncluded(
            $executionDate->copy()->subDays(static::DAYS_BEFORE)->startOfDay(),
            $executionDate->copy()->addDays(static::DAYS_AFTER)->endOfDay()
        );
    }

    protected function sumsToTransactionAmount(Collection $positions, Transaction $transaction): bool
    {
        return bccomp(
            bcabs(bcround(bcsum($positions->pluck('amount')), 2)),
            bcabs(bcround((string) $transaction->amount, 2)),
            2
        ) === 0;
    }

    protected function assign(PaymentRunPosition $position, Transaction $transaction): void
    {
        foreach ($position->orders as $order) {
            CreateOrderTransaction::make([
                'transaction_id' => $transaction->getKey(),
                'order_id' => $order->getKey(),
                'amount' => bcround((string) $order->pivot->amount, 2),
                'is_accepted' => true,
            ])
                ->validate()
                ->execute();
        }

        $position->orders()->updateExistingPivot($position->orders->modelKeys(), ['success' => true]);
    }
}
