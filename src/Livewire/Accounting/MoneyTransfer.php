<?php

namespace FluxErp\Livewire\Accounting;

use FluxErp\Enums\PaymentRunTypeEnum;
use FluxErp\Models\OrderType;
use FluxErp\States\Order\PaymentState\Open;
use FluxErp\States\Order\PaymentState\Overpaid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MoneyTransfer extends DirectDebit
{
    public array $enabledCols = [
        'invoice_number',
        'invoice_date',
        'payment_target_date',
        'payment_discount_target_date',
        'contact.customer_number',
        'address_invoice.name',
        'total_gross_price',
        'balance',
        'balance_due_discount',
        'commission',
    ];

    protected PaymentRunTypeEnum $paymentRunTypeEnum = PaymentRunTypeEnum::MoneyTransfer;

    protected function getBuilder(Builder $builder): Builder
    {
        [$outgoingOrderTypes, $incomingOrderTypes] = resolve_static(OrderType::class, 'query')
            ->where('is_active', true)
            ->get(['id', 'order_type_enum'])
            ->partition(fn (OrderType $orderType) => $orderType->order_type_enum->multiplier() < 0)
            ->map(fn (Collection $orderTypes) => $orderTypes->pluck('id'));

        return $builder
            ->where('balance', '<', 0)
            ->whereNotNull('invoice_number')
            ->where(fn (Builder $query) => $query
                ->whereIntegerInRaw('order_type_id', $outgoingOrderTypes)
                ->whereState('payment_state', Open::class)
                ->whereHas('paymentType', function (Builder $query): void {
                    $query->where('is_direct_debit', false)
                        ->where('requires_manual_transfer', true);
                })
                ->orWhere(fn (Builder $query) => $query
                    ->whereIntegerInRaw('order_type_id', $incomingOrderTypes)
                    ->whereState('payment_state', [Open::class, Overpaid::class])
                )
            );
    }
}
