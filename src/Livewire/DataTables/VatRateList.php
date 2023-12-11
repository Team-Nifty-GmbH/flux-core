<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\VatRate;
use TeamNiftyGmbH\DataTable\DataTable;

class VatRateList extends DataTable
{
    protected string $model = VatRate::class;

    public array $enabledCols = [
        'name',
        'rate_percentage',
    ];

    public array $formatters = [
        'rate_percentage' => 'percentage',
    ];
}
