<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Task;
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
        'responsibleUser.name',
        'priority',
        'state',
    ];

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('New'))
                ->color('primary')
                ->attributes([
                    'x-on:click' => "\$dispatch('new-task')",
                ]),
        ];
    }
}
