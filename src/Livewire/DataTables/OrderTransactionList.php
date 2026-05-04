<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Pivots\OrderTransaction;

class OrderTransactionList extends BaseDataTable
{
    public array $enabledCols = [
        'order_id',
        'transaction_id',
        'amount',
        'is_accepted',
    ];

    protected string $model = OrderTransaction::class;
}
