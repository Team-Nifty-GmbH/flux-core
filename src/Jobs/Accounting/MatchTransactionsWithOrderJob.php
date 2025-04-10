<?php

namespace FluxErp\Jobs\Accounting;

use Exception;
use FluxErp\Actions\OrderTransaction\CreateOrderTransaction;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Models\Order;
use FluxErp\Models\Pivots\OrderTransaction;
use FluxErp\Models\Transaction;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;

class MatchTransactionsWithOrderJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        public ?array $transactionIds = null
    ) {}

    public function handle(): void
    {
        resolve_static(Transaction::class, 'query')
            ->when($this->transactionIds, fn (Builder $query) => $query->whereKey($this->transactionIds))
            ->whereNot('balance', 0)
            ->whereDoesntHave('orders', fn (Builder $query) => $query->whereNot('is_accepted', true))
            ->cursor()
            ->each(function (Transaction $transaction): void {
                $this->processTransaction($transaction);
            });
    }

    protected function findMatchingOrders(Transaction $transaction): Collection
    {
        $contactId = resolve_static(ContactBankConnection::class, 'query')
            ->where('iban', $transaction->counterpart_iban)
            ->value('contact_id');

        $bookingDate = $transaction->booking_date->format('Y-m-d');

        $matches = collect();
        if ($transaction->purpose) {
            $order = $this->findOrderByPurpose($transaction, $contactId, $bookingDate);
            if ($order) {
                $matches->push($order);
            }
        }

        if ($contactId) {
            $order = $this->findOrderByContactAndBalance($transaction, $contactId, $bookingDate);
            if ($order) {
                $matches->push($order);
            }
        }

        if ($transaction->counterpart_name) {
            $order = $this->findOrderByCounterpartName($transaction, $bookingDate);
            if ($order) {
                $matches->push($order);
            }
        }

        return $matches;
    }

    protected function findOrderByContactAndBalance(Transaction $transaction, int $contactId, string $bookingDate): ?Order
    {
        return resolve_static(Order::class, 'query')
            ->whereNotNull('invoice_number')
            ->where('contact_id', $contactId)
            ->whereRaw('ROUND(balance, 2) = ?', $transaction->amount)
            ->orderByRaw('ABS(DATEDIFF(invoice_date, ?))', [$bookingDate])
            ->first();
    }

    protected function findOrderByCounterpartName(Transaction $transaction, string $bookingDate): ?Order
    {
        return resolve_static(Order::class, 'query')
            ->whereNotNull('invoice_number')
            ->whereHas(
                'contact.addresses',
                function (Builder $query) use ($transaction) {
                    return $query->whereRaw(
                        'LOWER(addresses.name) like ?',
                        '%' . strtolower($transaction->counterpart_name) . '%'
                    );
                }
            )
            ->orderByRaw('ABS(DATEDIFF(invoice_date, ?))', [$bookingDate])
            ->whereRaw('ROUND(balance, 2) = ?', $transaction->amount)
            ->first();
    }

    protected function findOrderByPurpose(Transaction $transaction, ?int $contactId, string $bookingDate): ?Order
    {
        $search = collect(array_merge(
            explode(' ', $transaction->purpose),
            explode('.', $transaction->purpose),
            explode('-', $transaction->purpose),
        ))
            ->filter()
            ->unique()
            ->values();

        $potentialMatches = collect();

        foreach ($search as $word) {
            $word = trim($word);
            if (
                strlen($word) < 5
                || $word === $transaction->counterpart_iban
                || $word === $transaction->counterpart_bic
            ) {
                continue;
            }

            try {
                $matches = $this->findPotentialOrderMatches($word, $transaction, $contactId);
                $potentialMatches = $potentialMatches->merge($matches);
            } catch (ModelNotFoundException) {
            }
        }

        if ($potentialMatches->isEmpty()) {
            return null;
        }

        return $potentialMatches
            ->sortBy([
                fn (Order $order) => abs(bcsub(round($order->balance ?? 0, 2), $transaction->amount)) > 0.01 ? 1 : 0,
                fn (Order $order) => $order->invoice_date
                    ? abs(strtotime($order->invoice_date) - strtotime($bookingDate))
                    : PHP_INT_MAX,
                fn (Order $order) => $contactId && $order->contact_id == $contactId ? 0 : 1,
            ])
            ->first();
    }

    protected function findPotentialOrderMatches(string $word, Transaction $transaction, ?int $contactId): Collection
    {
        $potentialMatches = collect();

        $matches = resolve_static(Order::class, 'query')
            ->whereRaw('LOWER(invoice_number) = ?', strtolower($word))
            ->when($contactId, fn ($query) => $query->where('contact_id', $contactId))
            ->get();

        $potentialMatches = $potentialMatches->merge($matches);

        if ($contactId) {
            $matches = resolve_static(Order::class, 'query')
                ->whereRaw('LOWER(invoice_number) = ?', strtolower($word))
                ->whereNot('contact_id', $contactId)
                ->get();

            $potentialMatches = $potentialMatches->merge($matches);
        }

        $matches = resolve_static(Order::class, 'query')
            ->where('invoice_number', 'like', '%' . $word . '%')
            ->whereRaw('ROUND(balance, 2) = ?', $transaction->amount)
            ->get();

        $potentialMatches = $potentialMatches->merge($matches);

        if ($contactId) {
            $matches = resolve_static(Order::class, 'query')
                ->whereNotNull('invoice_number')
                ->where('contact_id', $contactId)
                ->whereRaw('ROUND(balance, 2) = ?', $transaction->amount)
                ->get();

            $potentialMatches = $potentialMatches->merge($matches);
        }

        $matches = resolve_static(Order::class, 'query')
            ->whereNotNull('invoice_number')
            ->whereRelation('contact', 'customer_number', $word)
            ->whereRaw('ROUND(balance, 2) = ?', $transaction->amount)
            ->get();

        $potentialMatches = $potentialMatches->merge($matches);

        try {
            $matches = resolve_static(Order::class, 'query')
                ->whereNotNull('invoice_number')
                ->where('invoice_number', 'like', '%' . $word . '%')
                ->whereRaw('ROUND(total_gross_price, 2) = ?', $transaction->amount)
                ->get();

            $potentialMatches = $potentialMatches->merge($matches);
        } catch (Exception) {
        }

        return $potentialMatches->unique('id');
    }

    protected function processTransaction(Transaction $transaction): void
    {
        $orders = $this->findMatchingOrders($transaction);

        if ($orders->isEmpty()) {
            return;
        }

        $orders->each(function (Order $order) use ($transaction): void {
            if (! resolve_static(OrderTransaction::class, 'query')
                ->where('order_id', $order->getKey())
                ->where('transaction_id', $transaction->getKey())
                ->exists()
            ) {
                CreateOrderTransaction::make([
                    'transaction_id' => $transaction->getKey(),
                    'order_id' => $order->getKey(),
                    'amount' => $order->balance ?? 0,
                ])
                    ->validate()
                    ->execute();
            }
        });
    }
}
