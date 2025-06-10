<?php

namespace FluxErp\Jobs\Accounting;

use Cron\CronExpression;
use FluxErp\Actions\OrderTransaction\CreateOrderTransaction;
use FluxErp\Console\Scheduling\Repeatable;
use FluxErp\Enums\RepeatableTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Models\Order;
use FluxErp\Models\Pivots\OrderTransaction;
use FluxErp\Models\Transaction;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\MultipleRecordsFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MatchTransactionsWithOrderJob implements Repeatable, ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        public ?array $transactionIds = null
    ) {}

    public function __invoke(): void
    {
        $this->handle();
    }

    public static function defaultCron(): ?CronExpression
    {
        return new CronExpression('0 23 * * *');
    }

    public static function description(): ?string
    {
        return 'Try to match all open transactions with orders';
    }

    public static function isRepeatable(): bool
    {
        return true;
    }

    public static function name(): string
    {
        return Str::headline(class_basename(static::class));
    }

    public static function parameters(): array
    {
        return [];
    }

    public static function repeatableType(): RepeatableTypeEnum
    {
        return RepeatableTypeEnum::Invokable;
    }

    public function handle(): void
    {
        resolve_static(Transaction::class, 'query')
            ->when($this->transactionIds, fn (Builder $query) => $query->whereKey($this->transactionIds))
            ->whereNot('balance', 0)
            ->where('is_ignored', false)
            ->whereDoesntHave('orders', fn (Builder $query) => $query->where('is_accepted', false))
            ->latest()
            ->cursor()
            ->each(function (Transaction $transaction): void {
                $this->processTransaction($transaction);
            });
    }

    protected function findMatchingOrders(Transaction $transaction): Collection
    {
        $contactId = resolve_static(ContactBankConnection::class, 'query')
            ->latest()
            ->where('iban', $transaction->counterpart_iban)
            ->value('contact_id')
            ?? resolve_static(Order::class, 'query')
                ->latest()
                ->where('iban', $transaction->counterpart_iban)
                ->value('contact_id');

        try {
            $contactId ??= resolve_static(Address::class, 'query')
                ->whereRaw(
                    'JSON_CONTAINS(LOWER(search_aliases), LOWER(?))',
                    [json_encode($transaction->counterpart_name)]
                )
                ->sole('contact_id');
        } catch (ModelNotFoundException|MultipleRecordsFoundException) {
        }

        $bookingDate = $transaction->booking_date->format('Y-m-d');

        $matches = collect();
        if ($contactId) {
            $order = $this->findOrderByContactAndBalance($transaction, $contactId, $bookingDate);

            if ($order) {
                // exact match for contact and balance
                $matches->push($order);

                return $matches;
            }
        }

        if ($transaction->purpose) {
            $orders = $this->findOrderByPurpose($transaction, $contactId, $bookingDate);

            if ($orders?->isNotEmpty()) {
                $matches = $matches->merge($orders);
            }
        }

        if ($transaction->counterpart_name) {
            $order = $this->findOrderByCounterpartName($transaction, $bookingDate);

            if ($order) {
                $matches->push($order);
            }
        }

        return $matches->unique('id');
    }

    protected function findOrderByContactAndBalance(
        Transaction $transaction,
        int $contactId,
        string $bookingDate
    ): ?Order {
        return resolve_static(Order::class, 'query')
            ->whereNotNull('invoice_number')
            ->where('contact_id', $contactId)
            ->whereRaw('ROUND(balance, 2) = ROUND(?, 2)', $transaction->amount)
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

    protected function findOrderByPurpose(
        Transaction $transaction,
        ?int $contactId,
        string $bookingDate
    ): ?Collection {
        $search = collect(array_merge(
            explode(' ', $transaction->purpose),
            explode('.', $transaction->purpose),
            explode('-', $transaction->purpose),
            explode(',', $transaction->purpose),
        ))
            ->filter()
            ->map(fn (string $word) => trim($word))
            ->unique()
            ->values();

        $potentialMatches = collect();

        foreach ($search as $word) {
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
                fn (Order $order) => abs(
                    bcsub(
                        round($order->balance ?? 0, 2),
                        $transaction->amount
                    )
                ) > 0.01 ? 1 : 0,
                fn (Order $order) => $order->invoice_date
                    ? abs(strtotime($order->invoice_date) - strtotime($bookingDate))
                    : PHP_INT_MAX,
                fn (Order $order) => $contactId && $order->contact_id == $contactId ? 0 : 1,
            ])
            ->values();
    }

    protected function findPotentialOrderMatches(string $word, Transaction $transaction, ?int $contactId): Collection
    {
        return resolve_static(Order::class, 'query')
            ->where(function (Builder $query) use ($word, $transaction, $contactId): void {
                // exact invoice number match with non-zero balance
                $query->where(function (Builder $query) use ($word, $contactId): void {
                    $query->whereRaw('LOWER(invoice_number) = ?', strtolower($word))
                        ->whereNot('balance', 0)
                        ->when($contactId, fn (Builder $subQuery) => $subQuery->where('contact_id', $contactId));
                })
                    // partial invoice number match with exact amount
                    ->orWhere(function (Builder $query) use ($word, $transaction): void {
                        $query->where('invoice_number', 'like', '%' . $word . '%')
                            ->whereRaw('ROUND(balance, 2) = ROUND(?, 2)', $transaction->amount);
                    })
                    // contact id with exact amount (only if contactId exists)
                    ->when($contactId, function (Builder $query) use ($contactId, $transaction): void {
                        $query->orWhere(function (Builder $query) use ($contactId, $transaction): void {
                            $query->whereNotNull('invoice_number')
                                ->where('contact_id', $contactId)
                                ->whereRaw('ROUND(balance, 2) = ?', $transaction->amount);
                        });
                    })
                    // customer number relation with exact amount
                    ->orWhere(function (Builder $query) use ($word, $transaction): void {
                        $query->whereNotNull('invoice_number')
                            ->whereRelation('contact', 'customer_number', $word)
                            ->whereRaw('ROUND(balance, 2) = ?', $transaction->amount);
                    })
                    // partial invoice match with total gross price
                    ->orWhere(function (Builder $query) use ($word, $transaction): void {
                        $query->whereNotNull('invoice_number')
                            ->where('invoice_number', 'like', '%' . $word . '%')
                            ->whereRaw('ROUND(total_gross_price, 2) = ?', $transaction->amount);
                    });
            })
            ->get()
            ->unique('id');
    }

    protected function processTransaction(Transaction $transaction): void
    {
        $orders = $this->findMatchingOrders($transaction);

        $orders->each(function (Order $order) use ($transaction): void {
            if (! resolve_static(OrderTransaction::class, 'query')
                ->where('order_id', $order->getKey())
                ->where('transaction_id', $transaction->getKey())
                ->exists()
            ) {
                $transaction->refresh();
                $amount = $transaction->amount > 0
                    ? min($order->balance, $transaction->balance)
                    : max($order->balance, $transaction->balance);

                CreateOrderTransaction::make([
                    'transaction_id' => $transaction->getKey(),
                    'order_id' => $order->getKey(),
                    'amount' => $amount,
                ])
                    ->validate()
                    ->execute();
            }
        });
    }
}
