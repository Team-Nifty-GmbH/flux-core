<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\VatRate;

class VatRateList extends BaseDataTable
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
