<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use TeamNiftyGmbH\DataTable\Traits\HasEloquentListeners;

class TaskList extends DataTable
{
    use HasEloquentListeners;

    protected string $model = Task::class;

    public bool $showFilterInputs = true;

    public array $enabledCols = [
        'due_date',
        'name',
        'responsible_user.name',
        'priority',
        'state',
    ];

    public array $formatters = [
        'start_date' => 'date',
        'due_date' => 'date',
        'progress' => 'percentage',
    ];
}
