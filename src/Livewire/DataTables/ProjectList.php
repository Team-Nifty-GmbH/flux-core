<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Project;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Traits\HasEloquentListeners;

class ProjectList extends DataTable
{
    use HasEloquentListeners;

    protected string $model = Project::class;

    public array $enabledCols = [
        'project_number',
        'name',
        'state',
        'start_date',
        'end_date',
        'progress',
    ];
}
