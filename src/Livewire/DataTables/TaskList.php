<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Actions\WorkTime\CreateWorkTime;
use FluxErp\Models\Task;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class TaskList extends BaseDataTable
{
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

    public bool $showFilterInputs = true;

    protected string $model = Task::class;

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->icon('clock')
                ->text(__('Track Time'))
                ->when(fn () => resolve_static(CreateWorkTime::class, 'canPerformAction', [false]))
                ->xOnClick(<<<'JS'
                    $event.stopPropagation();
                    $dispatch(
                        'start-time-tracking',
                        {
                            trackable_type: 'FluxErp\\\Models\\\Task',
                            trackable_id: record.id,
                            name: record.name,
                            description: record.description
                        }
                    );
                JS),
        ];
    }
}
