<?php

namespace FluxErp\Livewire\Task;

use FluxErp\Actions\Task\CreateTask;
use FluxErp\Actions\Task\DeleteTask;
use FluxErp\Actions\Task\UpdateTask;
use FluxErp\Jobs\ExecuteActionsJob;
use FluxErp\Livewire\DataTables\TaskList as BaseTaskList;
use FluxErp\Livewire\Forms\TaskForm;
use FluxErp\Models\Task;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class TaskList extends BaseTaskList
{
    public array $availableStates = [];

    public ?string $selectedState = null;

    public TaskForm $task;

    protected ?string $includeBefore = 'flux::livewire.task.task-list';

    public function mount(): void
    {
        parent::mount();

        $this->isSelectable = true;

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
                ->wireClick('edit()'),
        ];
    }

    protected function getSelectedActions(): array
    {
        return [
            DataTableButton::make()
                ->icon('pencil')
                ->text(__('Change state'))
                ->color('indigo')
                ->when(fn () => resolve_static(UpdateTask::class, 'canPerformAction', [false]))
                ->wireClick('openChangeStateModal()'),
            DataTableButton::make()
                ->icon('trash')
                ->text(__('Delete'))
                ->color('red')
                ->when(fn () => resolve_static(DeleteTask::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'deleteSelected()',
                    'wire:flux-confirm.type.error' => __('wire:confirm.delete', ['model' => __('Tasks')]),
                ]),
        ];
    }

    #[Renderless]
    public function deleteSelected(): void
    {
        $taskIds = $this->getSelectedValues();

        if (blank($taskIds)) {
            return;
        }

        resolve_static(ExecuteActionsJob::class, 'dispatch', [
            DeleteTask::class,
            $taskIds,
            __('Deleting tasks'),
        ]);

        $this->reset('selected');
    }

    #[Renderless]
    public function openChangeStateModal(): void
    {
        $this->selectedState = null;

        $this->js(<<<'JS'
            $tsui.open.modal('change-task-state-modal');
        JS);
    }

    #[Renderless]
    public function changeState(): bool
    {
        if (! in_array($this->selectedState, Arr::pluck($this->availableStates, 'name'), true)) {
            return false;
        }

        $taskIds = $this->getSelectedValues();

        if (blank($taskIds)) {
            return false;
        }

        $payloads = resolve_static(Task::class, 'query')
            ->whereIntegerInRaw('id', $taskIds)
            ->get(['id', 'start_date', 'due_date'])
            ->map(fn (Task $task): array => [
                'id' => $task->getKey(),
                'state' => $this->selectedState,
                'start_date' => $task->start_date?->toDateString(),
                'due_date' => $task->due_date?->toDateString(),
            ])
            ->all();

        resolve_static(ExecuteActionsJob::class, 'dispatch', [
            UpdateTask::class,
            $payloads,
            __('Updating task state'),
        ]);

        $this->reset('selected', 'selectedState');

        return true;
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
    public function edit(): void
    {
        $this->task->reset();

        $this->js(<<<'JS'
            $tsui.open.modal('new-task-modal');
        JS);
    }
}
