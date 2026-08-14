<?php

namespace FluxErp\Support\Matching;

use FluxErp\Actions\OrderTransaction\CreateOrderTransaction;
use FluxErp\Enums\PaymentRunTypeEnum;
use FluxErp\Models\PaymentRunPosition;
use FluxErp\Models\Transaction;
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
        $runs = $this->openPositions($transaction)
            ->get()
            ->filter(fn (PaymentRunPosition $position) => $this->isInWindow($position, $transaction))
            ->groupBy('payment_run_id');

        foreach ($runs as $positions) {
            if ($positions->first()->paymentRun->is_single_booking) {
                continue;
            }

            $sum = $positions->reduce(
                fn (string $carry, PaymentRunPosition $position) => bcadd($carry, (string) $position->amount, 9),
                '0'
            );

            if (bccomp(bcabs(bcround($sum, 2)), bcabs(bcround((string) $transaction->amount, 2)), 2) === 0) {
                return $positions->values();
            }
        }

        return null;
    }

    protected function openPositions(Transaction $transaction): Builder
    {
        return resolve_static(PaymentRunPosition::class, 'query')
            ->where('amount', '!=', 0)
            ->whereHas(
                'paymentRun',
                fn (Builder $query) => $query
                    ->whereIn('state', [Pending::$name, Successful::$name])
                    ->whereIn('payment_run_type_enum', $this->runTypesMatchingDirection($transaction))
            )
            ->whereDoesntHave(
                'orders.transactions',
                fn (Builder $query) => $query->whereKey($transaction->getKey())
            );
    }

    protected function runTypesMatchingDirection(Transaction $transaction): array
    {
        $sign = bccomp((string) $transaction->amount, '0', 2);

        return array_column(
            array_filter(
                PaymentRunTypeEnum::cases(),
                fn (PaymentRunTypeEnum $case): bool => $case->expectedSign() === $sign
            ),
            'value'
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
        $sum = $positions->reduce(
            fn (string $carry, PaymentRunPosition $position) => bcadd($carry, $position->orders->reduce(
                fn (string $innerCarry, $order) => bcadd($innerCarry, (string) $order->pivot->amount, 9),
                '0'
            ), 9),
            '0'
        );

        return bccomp(bcabs(bcround($sum, 2)), bcabs(bcround((string) $transaction->amount, 2)), 2) === 0;
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
    }
}
