<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Order;
use TeamNiftyGmbH\DataTable\DataTable;
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
        'address_invoice.company',
        'address_invoice.firstname',
        'address_invoice.lastname',
        'total_net_price',
        'payment_state',
        'commission',
    ];

    public array $availableRelations = ['*'];

    public array $sortable = ['*'];

    public array $aggregatable = ['*'];

    public array $availableCols = ['*'];

    public bool $showModal = false;

    public function getTableActions(): array
    {
        $this->getIncludedRelations();
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
}
