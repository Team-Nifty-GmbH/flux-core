<?php

namespace FluxErp\Livewire\DataTables\Transactions;

use FluxErp\Livewire\DataTables\OrderList as BaseOrderList;
use FluxErp\Models\Order;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class OrderList extends BaseOrderList
{
    public array $enabledCols = [
        'invoice_number',
        'invoice_date',
        'address_invoice.company',
        'total_gross_price',
        'payment_state',
    ];

    public int $perPage = 5;

    public bool $hasNoRedirect = true;

    protected function getTableActions(): array
    {
        return [];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->icon('eye')
                ->attributes([
                    'x-on:click' => 'showOrder(record.id)',
                ]),
        ];
    }

    public function showOrder(Order $order): ?string
    {
        return $order->getUrl();
    }
}
