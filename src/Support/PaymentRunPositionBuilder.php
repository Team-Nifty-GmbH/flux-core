<?php

namespace FluxErp\Support;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Order;
use Illuminate\Support\Collection;

class PaymentRunPositionBuilder
{
    public function build(Collection $orders, Collection $creditNotes): array
    {
        return $orders
            ->merge($creditNotes)
            ->groupBy(fn (Order $order) => $this->groupKey($order))
            ->map(fn (Collection $group, string $key) => $this->toPosition($key, $group))
            ->filter(fn (array $position) => $position['orders'] !== [])
            ->values()
            ->toArray();
    }

    public function recap(array $rows): array
    {
        $rows = array_map(function (array $row) {
            $magnitude = bcabs($row['amount']);
            $magnitude = bccomp($magnitude, $row['max_amount'], 2) > 0
                ? bcround($row['max_amount'], 2)
                : bcround($magnitude, 2);

            $row['amount'] = bcround(bcmul($magnitude, (string) $row['multiplier'], 9), 2);

            return $row;
        }, $rows);

        $remaining = array_reduce(
            $rows,
            fn (string $carry, array $row) => $row['is_credit_note']
                ? $carry
                : bcadd($carry, bcabs($row['amount']), 9),
            '0'
        );

        return array_map(function (array $row) use (&$remaining) {
            if (! $row['is_credit_note']) {
                return $row;
            }

            $ownAmount = $row['amount'];
            $ownMagnitude = bcabs($ownAmount);
            $magnitude = bccomp($ownMagnitude, $remaining, 2) > 0 ? $remaining : $ownMagnitude;
            $remaining = bcsub($remaining, $magnitude, 9);

            $row['amount'] = bcround(bcmul($magnitude, (string) $row['multiplier'], 9), 2);
            $row['capped_from'] = bccomp($row['amount'], $ownAmount, 2) === 0 ? null : $ownAmount;

            return $row;
        }, $rows);
    }

    public function total(array $rows): string
    {
        return bcround(
            array_reduce(
                $rows,
                fn (string $carry, array $row) => bcadd($carry, $row['amount'], 9),
                '0'
            ),
            2
        );
    }

    public function purpose(array $invoiceNumbers): string
    {
        return mb_substr(implode(', ', $invoiceNumbers), 0, 140);
    }

    protected function groupKey(Order $order): string
    {
        return ($order->contact_id ?? 0) . '|' . strtoupper(
            str_replace(' ', '', $order->iban ?: $order->contactBankConnection?->iban ?? '')
        );
    }

    protected function toPosition(string $key, Collection $group): array
    {
        [$creditNotes, $invoices] = $group->partition(
            fn (Order $order) => $order->orderType?->order_type_enum === OrderTypeEnum::PurchaseRefund
        );

        $rows = [];

        foreach ($invoices as $invoice) {
            $rows[] = $this->row($invoice, $this->amountFor($invoice), false);
        }

        foreach ($creditNotes as $creditNote) {
            $rows[] = $this->row($creditNote, bcabs(bcround((string) $creditNote->balance, 2)), true);
        }

        $rows = $this->recap($rows);

        $first = $group->first();

        return [
            'key' => $key,
            'contact_id' => $first->contact_id,
            'contact_name' => $first->contact?->name ?? $first->addressInvoice?->name,
            'iban' => $first->iban ?: $first->contactBankConnection?->iban,
            'bic' => $first->bic ?: $first->contactBankConnection?->bic,
            'account_holder' => $first->account_holder
                ?: $first->contactBankConnection?->account_holder
                    ?: $first->addressInvoice?->name,
            'purpose' => $this->purpose(array_column($rows, 'invoice_number')),
            'amount' => $this->total($rows),
            'orders' => $rows,
        ];
    }

    protected function row(Order $order, string $amount, bool $isCreditNote): array
    {
        $multiplier = bccomp((string) $order->balance, '0', 2);

        return [
            'id' => $order->getKey(),
            'invoice_number' => $order->invoice_number,
            'is_credit_note' => $isCreditNote,
            'is_suggested' => false,
            'amount' => bcround(bcmul(bcround($amount, 2), (string) $multiplier), 2),
            'max_amount' => bcround($amount, 2),
            'multiplier' => $multiplier,
            'total_gross_price' => bcround((string) $order->total_gross_price, 2),
            'balance' => bcround((string) $order->balance, 2),
            'balance_due_discount' => $order->balance_due_discount
                ? bcround((string) $order->balance_due_discount, 2)
                : null,
            'payment_discount_target_date' => $order->payment_discount_target_date?->format('Y-m-d'),
            'payment_discount_percent' => $order->payment_discount_percent,
            'capped_from' => null,
        ];
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
}
