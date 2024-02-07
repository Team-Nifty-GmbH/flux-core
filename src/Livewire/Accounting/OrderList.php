<?php

namespace FluxErp\Livewire\Accounting;

use FluxErp\Actions\Transaction\CreateTransaction;
use FluxErp\Livewire\DataTables\OrderList as DataTableOrderList;
use Illuminate\Database\Eloquent\Builder;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class OrderList extends DataTableOrderList
{
    public bool $isSelectable = true;

    public array $enabledCols = [
        'invoice_number',
        'contact.customer_number',
        'address_invoice.name',
        'total_net_price',
        'balance',
        'commission',
    ];

    public int $perPage = 10;

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->whereNotNull('invoice_number');
    }

    public function getSelectedActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Assign'))
                ->color('primary')
                ->attributes([
                    'x-on:click' => '$wire.$parent.assignOrders($wire.selected).then(() => {$wire.selected = [];});',
                ])
                ->when(fn () => CreateTransaction::canPerformAction(false)),
        ];
    }
}
