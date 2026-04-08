<?php

namespace FluxErp\Livewire\Accounting;

use FluxErp\Livewire\DataTables\OrderList as DataTableOrderList;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Modelable;

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

    #[Modelable]
    public array $selected = [];

    public int $perPage = 10;

    protected function getSelectedActions(): array
    {
        return [];
    }

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->whereNotNull('invoice_number');
    }
}
