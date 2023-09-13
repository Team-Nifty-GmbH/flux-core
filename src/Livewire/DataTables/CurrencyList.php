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

    public function mount(): void
    {
        $attributes = ModelInfo::forModel($this->model)->attributes;

        $this->availableCols = $attributes
            ->pluck('name')
            ->toArray();

        parent::mount();
    }
}
