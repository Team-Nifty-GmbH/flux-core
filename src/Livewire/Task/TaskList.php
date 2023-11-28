<?php

namespace FluxErp\Livewire\Task;

use FluxErp\Livewire\DataTables\TaskList as BaseTaskList;

class TaskList extends BaseTaskList
{
    public array $filters = [
        [
            'column' => 'project_id',
            'operator' => 'is null',
        ],
    ];
}
