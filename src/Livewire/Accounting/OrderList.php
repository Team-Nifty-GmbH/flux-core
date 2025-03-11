<?php

namespace FluxErp\Livewire\Accounting;

use FluxErp\Actions\Transaction\CreateTransaction;
use FluxErp\Livewire\DataTables\OrderList as DataTableOrderList;
use Illuminate\Database\Eloquent\Builder;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class OrderList extends DataTableOrderList
{
    public array $enabledCols = [
        'invoice_number',
        'contact.customer_number',
        'address_invoice.name',
        'total_net_price',
        'balance',
        'commission',
    ];

    public bool $isSelectable = true;

    public int $perPage = 10;

    protected function getSelectedActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Assign'))
                ->color('indigo')
                ->attributes([
                    'x-on:click' => '$wire.$parent.assignOrders($wire.selected).then(() => {$wire.selected = [];});',
                ])
                ->when(fn () => resolve_static(CreateTransaction::class, 'canPerformAction', [false])),
        ];
    }

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->whereNotNull('invoice_number');
    }
}
