<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Currency;

class CurrencyList extends BaseDataTable
{
    protected string $model = Currency::class;

    public array $enabledCols = [
        'name',
        'iso',
        'symbol',
    ];
}
