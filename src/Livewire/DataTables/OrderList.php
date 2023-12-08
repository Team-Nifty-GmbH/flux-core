<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Order;
use TeamNiftyGmbH\DataTable\DataTable;

class OrderList extends DataTable
{
    protected string $model = Order::class;

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

    public bool $showModal = false;

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
