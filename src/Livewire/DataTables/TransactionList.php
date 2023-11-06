<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Transaction;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Helpers\ModelInfo;

class TransactionList extends DataTable
{
    protected string $model = Transaction::class;

    public array $enabledCols = [
        'amount',
        'purpose',
        'created_at',
    ];

    public array $availableRelations = ['*'];

    public array $sortable = ['*'];

    public array $aggregatable = ['*'];

    public array $availableCols = ['*'];
}
