<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Currency;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Helpers\ModelInfo;

class CurrencyList extends DataTable
{
    protected string $model = Currency::class;

    public array $enabledCols = [
        'name',
        'iso',
        'symbol',
    ];
    public array $availableRelations = ['*'];

    public array $sortable = ['*'];

    public array $aggregatable = ['*'];

    public array $availableCols = ['*'];
}
