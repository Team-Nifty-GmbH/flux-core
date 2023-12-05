<?php

namespace FluxErp\Livewire\Task;

use FluxErp\Livewire\DataTables\TaskList as BaseTaskList;
use FluxErp\Livewire\Forms\TaskForm;
use FluxErp\Models\Task;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class TaskList extends BaseTaskList
{
    protected string $view = 'flux::livewire.task.task-list';

    public TaskForm $task;

    public array $availableStates = [];

    public function mount(): void
    {
        parent::mount();

        $this->task->additionalColumns = array_fill_keys(
            Task::additionalColumnsQuery()->pluck('name')?->toArray() ?? [],
            null
        );

        $this->availableStates = Task::getStatesFor('state')->map(function ($state) {
            return [
                'label' => __(ucfirst(str_replace('_', ' ', $state))),
                'name' => $state,
            ];
        })->toArray();
    }

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

    public function resetForm(): void
    {
        $this->task->reset();
        $this->task->additionalColumns = array_fill_keys(
            Task::additionalColumnsQuery()->pluck('name')?->toArray() ?? [],
            null
        );

        $this->skipRender();
    }
}
