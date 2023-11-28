<?php

namespace FluxErp\Livewire\Project;

use FluxErp\Livewire\DataTables\TaskList as BaseTaskList;

class ProjectTaskList extends BaseTaskList
{
    public array $filters = [
        [
            'column' => 'project_id',
            'operator' => 'is not null',
        ],
    ];

    public function getTableActions(): array
    {
        return [];
    }
}
