<?php

namespace FluxErp\Livewire\DataTables\Order;

class TransactionList extends \FluxErp\Livewire\DataTables\TransactionList
{
    public array $enabledCols = [
        'amount',
        'purpose',
        'created_at',
    ];
}
