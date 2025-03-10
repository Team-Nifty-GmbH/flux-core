<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Task;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class TaskList extends BaseDataTable
{
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

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->icon('clock')
                ->text(__('Track Time'))
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
