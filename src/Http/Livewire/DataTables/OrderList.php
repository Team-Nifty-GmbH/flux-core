<?php

namespace FluxErp\Http\Livewire\DataTables;

use FluxErp\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Helpers\ModelInfo;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class OrderList extends DataTable
{
    protected string $model = Order::class;

    public bool $showFilterInputs = true;

    public array $enabledCols = [
        'order_type.name',
        'order_date',
        'order_number',
        'invoice_number',
        'contact.customer_number',
        'address_invoice.name',
        'total_net_price',
        'payment_state',
        'commission',
    ];

    public array $availableRelations = ['*'];

    public array $sortable = ['*'];

    public array $aggregatable = [
        'total_net_price',
        'total_gross_price',
        'total_vats',
    ];

    public bool $showModal = false;

    public function mount(): void
    {
        $attributes = ModelInfo::forModel(Order::class)->attributes;

        $this->availableCols = array_merge(
            $attributes->pluck('name')->toArray(),
            ['currency.iso'],
        );

        parent::mount();
    }

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->color('primary')
                ->label(__('New order'))
                ->icon('plus')
                ->attributes([
                    'x-on:click' => "\$dispatch('create-order')",
                ]),
        ];
    }

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->with(['contact:id,customer_number', 'orderType:id,name', 'currency:id,iso']);
    }

    public function getFormatters(): array
    {
        $formatters = parent::getFormatters();

        array_walk($formatters, function (&$formatter) {
            if ($formatter === 'money') {
                $formatter = ['coloredMoney', ['property' => 'currency.iso']];
            }
        });

        return $formatters;
    }

    public function getReturnKeys(): array
    {
        return array_merge(parent::getReturnKeys(), ['currency.iso']);
    }

    public function getFilterableColumns(?string $name = null): array
    {
        return $this->availableCols;
    }
}
