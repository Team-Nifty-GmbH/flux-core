<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Target;

class TargetList extends BaseDataTable
{
    public array $enabledCols = [
        'start_date',
        'end_date',
        'model_type',
        'aggregate_column',
        'target_value',
        'users.name',
    ];

    protected string $model = Target::class;
}
