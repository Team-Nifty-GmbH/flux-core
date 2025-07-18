<?php

namespace FluxErp\Livewire\Accounting;

use FluxErp\Actions\PaymentRun\CreatePaymentRun;
use FluxErp\Enums\PaymentRunTypeEnum;
use FluxErp\Enums\SepaMandateTypeEnum;
use FluxErp\Livewire\DataTables\OrderList;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\States\Order\PaymentState\Paid;
use FluxErp\States\PaymentRun\Open;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
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

    protected function getSelectedActions(): array
    {
        return [
            DataTableButton::make()
                ->color('indigo')
                ->text(__('Create Payment Run'))
                ->attributes([
                    'wire:click' => 'createPaymentRun',
                    'wire:flux-confirm' => __('Create Payment Run|Do you really want to create the Payment Run?|Cancel|Yes'),
                ])
                ->when(resolve_static(CreatePaymentRun::class, 'canPerformAction', [false])),
        ];
    }

    public function createPaymentRun(): void
    {
        $orderPayments = $this->getSelectedModelsQuery()
            ->with('contactBankConnection.sepaMandates')
            ->whereHas(
                'contactBankConnection.sepaMandates',
                fn (Builder $query) => $query->whereNotNull('signed_date')
            )
            ->get()
            ->map(fn (Order $order) => [
                'order_id' => $order->id,
                'amount' => bcround($order->balance, 2),
                'type' => $order->contactBankConnection?->sepaMandates->last()?->sepa_mandate_type_enum ?? null,
            ])
            ->groupBy('type')
            ->toArray();

        foreach ($orderPayments as $type => $payments) {
            try {
                CreatePaymentRun::make([
                    'state' => Open::$name,
                    'payment_run_type_enum' => PaymentRunTypeEnum::DirectDebit,
                    'sepa_mandate_type_enum' => SepaMandateTypeEnum::tryFrom($type) ?? SepaMandateTypeEnum::BASIC,
                    'orders' => $payments,
                ])
                    ->checkPermission()
                    ->validate()
                    ->execute();
            } catch (ValidationException|UnauthorizedException $e) {
                exception_to_notifications($e, $this);

                return;
            }
        }

        $this->reset('selected');

        $this->toast()
            ->success(__(':model created', ['model' => __('Payment Run')]))
            ->send();

        $this->loadData();
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
