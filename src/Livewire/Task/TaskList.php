<?php

namespace FluxErp\Livewire\Task;

use FluxErp\Actions\Task\CreateTask;
use FluxErp\Livewire\DataTables\TaskList as BaseTaskList;
use FluxErp\Livewire\Forms\TaskForm;
use FluxErp\Models\Task;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class TaskList extends BaseTaskList
{
    public array $availableStates = [];

    public TaskForm $task;

    protected ?string $includeBefore = 'flux::livewire.task.task-list';

    public function mount(): void
    {
        parent::mount();

        $this->task->additionalColumns = array_fill_keys(
            resolve_static(Task::class, 'additionalColumnsQuery')->pluck('name')?->toArray() ?? [],
            null
        );

        $this->availableStates = app(Task::class)
            ->getStatesFor('state')
            ->map(function (string $state) {
                return [
                    'label' => __(Str::headline($state)),
                    'name' => $state,
                ];
            })
            ->toArray();
    }

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Create'))
                ->color('indigo')
                ->icon('plus')
                ->when(resolve_static(CreateTask::class, 'canPerformAction', [false]))
                ->wireClick('show'),
        ];
    }

    #[Renderless]
    public function save(): bool
    {
        try {
            $this->task->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    #[Renderless]
    public function show(): void
    {
        $this->task->reset();
        $this->task->additionalColumns = array_fill_keys(
            resolve_static(Task::class, 'additionalColumnsQuery')->pluck('name')?->toArray() ?? [],
            null
        );

        $this->js(<<<'JS'
            $modalOpen('new-task-modal');
        JS);
    }
}
