<?php

namespace FluxErp\Support;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Order;
use Illuminate\Support\Collection;

class PaymentRunPositionBuilder
{
    public const string PLACEHOLDER_END_TO_END_ID = 'PR000-000';

    public const int END_TO_END_ID_MAX_LENGTH = 35;

    public const int PURPOSE_MAX_LENGTH = 140;

    public function build(Collection $orders, Collection $creditNotes): array
    {
        $suggestedIds = $creditNotes->pluck('id')->all();

        return $orders
            ->merge($creditNotes)
            ->groupBy(fn (Order $order) => $this->groupKey($order))
            ->map(fn (Collection $group, string $key) => $this->toPosition($key, $group, $suggestedIds))
            ->filter(fn (array $position) => $position['orders'] !== [])
            ->values()
            ->toArray();
    }

    public function purpose(array $invoiceNumbers, ?string $endToEndId = null): string
    {
        $invoiceNumbers = array_values(array_unique(array_filter($invoiceNumbers)));
        $listed = implode(', ', $invoiceNumbers);

        if (mb_strlen($listed) <= static::PURPOSE_MAX_LENGTH) {
            return $listed;
        }

        $budget = static::PURPOSE_MAX_LENGTH
            - mb_strlen(' (+' . count($invoiceNumbers) . ') ')
            - static::END_TO_END_ID_MAX_LENGTH;

        $fitting = [];
        foreach ($invoiceNumbers as $invoiceNumber) {
            if (mb_strlen(implode(', ', [...$fitting, $invoiceNumber])) > $budget) {
                break;
            }

            $fitting[] = $invoiceNumber;
        }

        return trim(
            implode(', ', $fitting)
            . ' (+' . (count($invoiceNumbers) - count($fitting)) . ') '
            . $endToEndId
        );
    }

    public function recap(array $rows): array
    {
        $remaining = '0';

        foreach ($rows as $index => $row) {
            $amount = bcabs($row['amount']);
            $amount = bccomp($amount, $row['max_amount'], 2) === 1 ? $row['max_amount'] : $amount;

            $rows[$index]['amount'] = bcround(bcmul(bcround($amount, 2), (string) $row['multiplier'], 9), 2);

            if (! $row['is_credit_note']) {
                $remaining = bcadd($remaining, bcabs($rows[$index]['amount']), 9);
            }
        }

        foreach ($rows as $index => $row) {
            if (! $row['is_credit_note']) {
                continue;
            }

            $amount = bcabs($row['amount']);
            $applied = bccomp($amount, $remaining, 2) === 1 ? $remaining : $amount;
            $remaining = bcsub($remaining, $applied, 9);

            $rows[$index]['amount'] = bcround(bcmul($applied, (string) $row['multiplier'], 9), 2);
            $rows[$index]['capped_from'] = bccomp($rows[$index]['amount'], $row['amount'], 2) === 0
                ? null
                : $row['amount'];
        }

        return $rows;
    }

    public function total(array $rows): string
    {
        return bcround(bcsum(array_column($rows, 'amount')), 2);
    }

    protected function amountFor(Order $order): string
    {
        $amount = $order->balance;

        if ($order->payment_discount_target_date
            && $order->payment_discount_target_date->greaterThanOrEqualTo(now()->endOfDay())
            && $order->balance_due_discount
        ) {
            $amount = $order->balance_due_discount;
        }

        return bcabs(bcround($amount, 2));
    }

    protected function groupKey(Order $order): string
    {
        return ($order->contact_id ?? 0) . '|' . strtoupper(
            str_replace(' ', '', data_get($order->paymentRecipient(), 'iban') ?? '')
        );
    }

    protected function row(Order $order, string $amount, bool $isCreditNote, bool $isSuggested): array
    {
        $multiplier = bccomp((string) $order->balance, '0', 2);

        return [
            'id' => $order->getKey(),
            'payment_discount_percent' => $order->payment_discount_percent,
            'total_gross_price' => bcround((string) $order->total_gross_price, 2),
            'balance' => bcround((string) $order->balance, 2),
            'balance_due_discount' => $order->balance_due_discount
                ? bcround((string) $order->balance_due_discount, 2)
                : null,
            'invoice_number' => $order->invoice_number,
            'amount' => bcround(bcmul(bcround($amount, 2), (string) $multiplier), 2),
            'max_amount' => bcround($amount, 2),
            'multiplier' => $multiplier,
            'is_credit_note' => $isCreditNote,
            'is_suggested' => $isSuggested,
        ];
    }

    protected function toPosition(string $key, Collection $group, array $suggestedIds): array
    {
        [$creditNotes, $invoices] = $group->partition(
            fn (Order $order) => $order->orderType?->order_type_enum === OrderTypeEnum::PurchaseRefund
        );

        $rows = [];
        foreach ($invoices as $invoice) {
            $rows[] = $this->row($invoice, $this->amountFor($invoice), false, false);
        }

        foreach ($creditNotes as $creditNote) {
            $rows[] = $this->row(
                $creditNote,
                bcabs(bcround((string) $creditNote->balance, 2)),
                true,
                in_array($creditNote->getKey(), $suggestedIds, true)
            );
        }

        $rows = $this->recap($rows);
        $first = $group->first();

        return [
            'key' => $key,
            'contact_name' => $first->addressInvoice?->name,
            'iban' => data_get($first->paymentRecipient(), 'iban'),
            'purpose' => $this->purpose(array_column($rows, 'invoice_number'), static::PLACEHOLDER_END_TO_END_ID),
            'amount' => $this->total($rows),
            'orders' => $rows,
        ];
    }
}
