<?php

namespace FluxErp\Http\Livewire\DataTables;

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

    public function mount(): void
    {
        $attributes = ModelInfo::forModel($this->model)->attributes;

        $this->availableCols = $attributes
            ->pluck('name')
            ->toArray();

        parent::mount();
    }
}
