<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\FixedAsset;

class FixedAssetList extends BaseDataTable
{
    public array $enabledCols = [
        'number',
        'name',
        'acquired_at',
        'acquisition_cost',
        'book_value',
        'useful_life_months',
        'disposed_at',
    ];

    public array $formatters = [
        'acquired_at' => 'date',
        'acquisition_cost' => 'money',
        'residual_value' => 'money',
        'book_value' => 'money',
        'depreciation_starts_at' => 'date',
        'disposed_at' => 'date',
    ];

    public array $sortable = [
        'number',
        'name',
        'acquired_at',
        'acquisition_cost',
        'book_value',
    ];

    protected string $model = FixedAsset::class;
}
