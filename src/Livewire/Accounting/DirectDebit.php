<?php

namespace FluxErp\Livewire\Accounting;

use FluxErp\Actions\PaymentRun\CreatePaymentRun;
use FluxErp\Enums\PaymentRunTypeEnum;
use FluxErp\Livewire\DataTables\OrderList;
use FluxErp\Models\OrderType;
use FluxErp\States\Order\PaymentState\Paid;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Renderless;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class DirectDebit extends OrderList
{
    public array $accounts = [];

    public array $enabledCols = [
        'invoice_number',
        'invoice_date',
        'contact.customer_number',
        'address_invoice.name',
        'total_gross_price',
        'balance',
        'commission',
        'contact_bank_connection.sepa_mandates.signed_date',
        'contact_bank_connection.sepa_mandates.sepa_mandate_type_enum',
    ];

    protected PaymentRunTypeEnum $paymentRunTypeEnum = PaymentRunTypeEnum::DirectDebit;

    protected function getSelectedActions(): array
    {
        return [
            DataTableButton::make()
                ->color('indigo')
                ->text(__('Create Payment Run'))
                ->wireClick('createPaymentRun')
                ->when(resolve_static(CreatePaymentRun::class, 'canPerformAction', [false])),
        ];
    }

    #[Renderless]
    public function createPaymentRun(): void
    {
        $selectedOrders = $this->getSelectedModelsQuery()->get($this->modelKeyName);

        if ($selectedOrders->isEmpty()) {
            $this->toast()
                ->error(__('Please select at least one order.'))
                ->send();

            return;
        }

        session(['payment_run_preview_orders' => $selectedOrders->toArray()]);
        session(['payment_run_type_enum' => $this->paymentRunTypeEnum]);

        $this->redirectRoute('accounting.payment-run-preview', navigate: true);
    }

    protected function getBuilder(Builder $builder): Builder
    {
        $orderTypes = resolve_static(OrderType::class, 'query')
            ->where('is_active', true)
            ->get(['id', 'order_type_enum'])
            ->filter(fn (OrderType $orderType) => ! $orderType->order_type_enum->isPurchase()
                && $orderType->order_type_enum->multiplier() > 0
            )
            ->pluck('id');

        return $builder->whereRelation('paymentType', 'is_direct_debit', true)
            ->where(function (Builder $query): void {
                $query
                    ->whereHas(
                        'paymentRuns',
                        fn (Builder $builder) => $builder->whereNotIn('state', ['open', 'successful', 'pending'])
                    )
                    ->orWhereDoesntHave('paymentRuns');
            })
            ->where('balance', '>', 0)
            ->whereNotState('payment_state', Paid::class)
            ->whereNotNull('invoice_number')
            ->whereIntegerInRaw('order_type_id', $orderTypes);
    }
}
