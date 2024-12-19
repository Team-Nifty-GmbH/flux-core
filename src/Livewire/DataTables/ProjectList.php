<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Project;

class ProjectList extends BaseDataTable
{
    protected string $model = Project::class;

    public array $enabledCols = [
        'project_number',
        'name',
        'state',
        'start_date',
        'end_date',
        'progress',
    ];

    public array $formatters = [
        'progress' => 'progressPercentage',
    ];
}
