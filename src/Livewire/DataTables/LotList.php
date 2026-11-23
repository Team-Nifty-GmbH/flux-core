<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Lot;

class LotList extends BaseDataTable
{
    public array $enabledCols = [
        'lot_number',
        'product.name',
        'produced_at',
        'expires_at',
        'blocked_at',
    ];

    public array $formatters = [
        'produced_at' => 'date',
        'expires_at' => 'date',
        'blocked_at' => 'datetime',
    ];

    protected string $model = Lot::class;
}
