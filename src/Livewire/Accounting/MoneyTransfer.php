<?php

namespace FluxErp\Livewire\Accounting;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Enums\PaymentRunTypeEnum;
use FluxErp\Models\OrderType;
use FluxErp\States\Order\PaymentState\Open;
use Illuminate\Database\Eloquent\Builder;

class MoneyTransfer extends DirectDebit
{
    public array $enabledCols = [
        'invoice_number',
        'invoice_date',
        'payment_target_date',
        'payment_discount_target_date',
        'contact.customer_number',
        'address_invoice.name',
        'order_type.name',
        'total_gross_price',
        'balance',
        'balance_due_discount',
        'commission',
    ];

    protected PaymentRunTypeEnum $paymentRunTypeEnum = PaymentRunTypeEnum::MoneyTransfer;

    protected function getBuilder(Builder $builder): Builder
    {
        $activeOrderTypes = resolve_static(OrderType::class, 'query')
            ->where('is_active', true)
            ->get(['id', 'order_type_enum']);

        $payableTypeIds = $activeOrderTypes
            ->filter(fn (OrderType $orderType) => $orderType->order_type_enum->multiplier() < 0)
            ->pluck('id');

        $refundTypeIds = $activeOrderTypes
            ->filter(fn (OrderType $orderType) => $orderType->order_type_enum === OrderTypeEnum::PurchaseRefund)
            ->pluck('id');

        return $builder
            ->whereHas('paymentType', function (Builder $query): void {
                $query->where('is_direct_debit', false)
                    ->where('requires_manual_transfer', true);
            })
            ->whereState('payment_state', Open::class)
            ->whereNotNull('invoice_number')
            ->where(function (Builder $query) use ($payableTypeIds, $refundTypeIds): void {
                $query->where(function (Builder $query) use ($payableTypeIds): void {
                    $query->whereIntegerInRaw('order_type_id', $payableTypeIds)
                        ->where('balance', '<', 0);
                })
                    ->orWhere(function (Builder $query) use ($refundTypeIds): void {
                        $query->whereIntegerInRaw('order_type_id', $refundTypeIds)
                            ->where('balance', '>', 0);
                    });
            });
    }
}
