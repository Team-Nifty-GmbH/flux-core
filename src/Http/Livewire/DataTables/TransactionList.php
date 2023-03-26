<?php

namespace FluxErp\Http\Livewire\DataTables;

use FluxErp\Models\Transaction;
use TeamNiftyGmbH\DataTable\DataTable;

class TransactionList extends DataTable
{
    protected string $model = Transaction::class;

    public array $enabledCols = [
        'amount',
        'purpose',
        'created_at',
    ];

    public function mount(): void
    {
        parent::mount();
    }
}
