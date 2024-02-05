<?php

namespace FluxErp\Jobs\Accounting;

use FluxErp\Actions\ContactBankConnection\CreateContactBankConnection;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Models\Order;
use FluxErp\Models\Transaction;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\MultipleRecordsFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Validation\ValidationException;

class MatchTransactionsWithOrderJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        public array $transactionIds
    ) {
    }

    public function handle(): void
    {
        $transactions = Transaction::query()
            ->whereIntegerInRaw('id', $this->transactionIds)
            ->get();

        foreach ($transactions as $transaction) {
            if ($transaction->order_id) {
                continue;
            }

            $order = null;
            $contactId = ContactBankConnection::query()
                ->where('iban', $transaction->counterpart_iban)
                ->value('contact_id');
            $bookingDate = $transaction->booking_date->format('Y-m-d');

            if ($transaction->purpose) {
                $search = collect(array_merge(
                    explode(' ', $transaction->purpose),
                    explode('.', $transaction->purpose),
                    explode('-', $transaction->purpose),
                ))->filter()->unique()->values();

                foreach ($search as $word) {
                    $word = trim($word);
                    if (strlen($word) < 4) {
                        continue;
                    }

                    try {
                        $order = Order::query()
                            ->whereRaw('LOWER(invoice_number) = ?', strtolower($word))
                            ->orderByRaw('ABS(DATEDIFF(invoice_date, ?))', [$bookingDate])
                            ->when($contactId, fn ($query) => $query->where('contact_id', $contactId))
                            ->first()
                            ?? ($contactId
                                ? Order::query()
                                    ->whereRaw('LOWER(invoice_number) = ?', strtolower($word))
                                    ->orderByRaw('ABS(DATEDIFF(invoice_date, ?))', [$bookingDate])
                                    ->first()
                                : null
                            )
                            ?? Order::query()
                                ->where('invoice_number', 'like', '%' . $word . '%')
                                ->whereRaw('ROUND(balance, 2) = ?', $transaction->amount)
                                ->orderByRaw('ABS(DATEDIFF(invoice_date, ?))', [$bookingDate])
                                ->first()
                            ?? Order::query()
                                ->whereNotNull('invoice_number')
                                ->where('contact_id', $contactId)
                                ->whereRaw('ROUND(balance, 2) = ?', $transaction->amount)
                                ->orderByRaw('ABS(DATEDIFF(invoice_date, ?))', [$bookingDate])
                                ->first()
                            ?? Order::query()
                                ->whereNotNull('invoice_number')
                                ->whereRelation('contact', 'customer_number', $word)
                                ->whereRaw('ROUND(balance, 2) = ?', $transaction->amount)
                                ->orderByRaw('ABS(DATEDIFF(invoice_date, ?))', [$bookingDate])
                                ->first()
                            ?? Order::query()
                                ->where('invoice_number', 'like', '%' . $word . '%')
                                ->whereRaw('ROUND(total_gross_price, 2) = ?', $transaction->amount)
                                ->sole();
                    } catch (MultipleRecordsFoundException|ModelNotFoundException) {
                    }

                    if ($order) {
                        break;
                    }
                }
            }

            // try to match balance and contact
            if (! $order && $contactId) {
                $order = Order::query()
                    ->whereNotNull('invoice_number')
                    ->where('contact_id', $contactId)
                    ->whereRaw('ROUND(balance, 2) = ?', $transaction->amount)
                    ->orderByRaw('ABS(DATEDIFF(invoice_date, ?))', [$bookingDate])
                    ->first();
            }

            // try to match counterpart name
            if (! $order) {
                $order = Order::query()
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

            if ($order) {
                $transaction->order()->associate($order);
                $transaction->save();

                $order->calculatePaymentState()->save();
            }

            if ($order && ! $contactId && $transaction->counterpart_iban) {
                // add the found bank connection to the contact
                try {
                    CreateContactBankConnection::make([
                        'contact_id' => $order->contact_id,
                        'iban' => $transaction->counterpart_iban,
                        'account_holder' => $transaction->counterpart_name,
                        'bank_name' => $transaction->counterpart_bank_name,
                        'bic' => $transaction->counterpart_bank_bic,
                    ])->validate()->execute();
                } catch (ValidationException) {
                }
            }
        }
    }
}
