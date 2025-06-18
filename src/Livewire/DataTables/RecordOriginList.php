<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\RecordOrigin;

class RecordOriginList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'model_type',
        'is_active',
    ];

    protected string $model = RecordOrigin::class;
}
