<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Currency;

class CurrencyList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'iso',
        'symbol',
    ];

    protected string $model = Currency::class;
}
