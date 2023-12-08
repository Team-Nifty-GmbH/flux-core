<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Currency;
use TeamNiftyGmbH\DataTable\DataTable;

class CurrencyList extends DataTable
{
    protected string $model = Currency::class;

    public array $enabledCols = [
        'name',
        'iso',
        'symbol',
    ];
}
