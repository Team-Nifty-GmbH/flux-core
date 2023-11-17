<?php

namespace FluxErp\Livewire\DataTables\Order;

class TransactionList extends \FluxErp\Livewire\DataTables\TransactionList
{
    public ?string $cacheKey = 'flux-core.livewire.data-tables.order.transaction-list';

    public array $enabledCols = [
        'amount',
        'purpose',
        'created_at',
    ];
}
