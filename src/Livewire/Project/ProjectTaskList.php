<?php

namespace FluxErp\Livewire\Project;

use FluxErp\Actions\Task\UpdateTask;
use FluxErp\Htmlables\TabButton;
use FluxErp\Livewire\DataTables\TaskList as BaseTaskList;
use FluxErp\Livewire\Forms\TaskForm;
use FluxErp\Models\Task;
use FluxErp\States\Task\TaskState;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\DataTable\DataTableHasFormEdit;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;

class ProjectTaskList extends BaseTaskList
{
    use Actions, DataTableHasFormEdit, WithTabs {
        DataTableHasFormEdit::edit as editForm;
    }

    public array $availableStates = [];

    public bool $hasNoRedirect = true;

    public ?int $projectId;

    #[DataTableForm]
    public TaskForm $task;

    public string $taskTab = 'task.general';

    protected ?string $includeBefore = 'flux::livewire.project.project-task-list';

    public function mount(): void
    {
        parent::mount();

        $this->task->project_id = $this->projectId;

        $this->availableStates = TaskState::all()
            ->map(fn (string $stateClass, string $morphName) => [
                'label' => __(Str::headline($morphName)),
                'name' => $morphName,
                'order' => $stateClass::$order,
            ])
            ->sortBy('order')
            ->values()
            ->toArray();
    }

    #[Renderless]
    public function edit(string|int|null $id = null): void
    {
        $this->editForm($id);
        $this->task->project_id = $this->projectId;
    }

    #[Renderless]
    public function getTabs(): array
    {
        return [
            TabButton::make('task.general')
                ->text(__('General')),
            TabButton::make('task.comments')
                ->isLivewireComponent()
                ->wireModel('task.id')
                ->text(__('Comments'))
                ->attributes([
                    'x-bind:disabled' => '! $wire.task.id',
                ]),
            TabButton::make('task.media')
                ->text(__('Media'))
                ->isLivewireComponent()
                ->wireModel('task.id')
                ->attributes([
                    'x-bind:disabled' => '! $wire.task.id',
                ]),
        ];
    }

    public function kanbanMoveItem(int|string $id, string $targetLane): void
    {
        try {
            $task = resolve_static(Task::class, 'query')
                ->whereKey($id)
                ->first(['id', 'state', 'start_date', 'due_date']);

            if (is_null($task) || $targetLane === $task->state::$name) {
                return;
            }

            resolve_static(UpdateTask::class, 'make', [[
                'id' => $task->getKey(),
                'state' => $targetLane,
                'start_date' => $task->start_date,
                'due_date' => $task->due_date,
            ]])
                ->checkPermission()
                ->validate()
                ->execute();

            $this->toast()
                ->success(__(':model saved', ['model' => __('Task')]))
                ->send();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);
        }
    }

    public function sortRows(int|string $id, int $position): void
    {
        $task = resolve_static(Task::class, 'query')
            ->whereKey($id)
            ->first(['id', 'state']);

        if (is_null($task)) {
            return;
        }

        $stateName = $task->state::$name;

        $laneTasks = resolve_static(Task::class, 'query')
            ->where('project_id', $this->projectId)
            ->where('state', $stateName)
            ->orderBy('priority')
            ->pluck('id')
            ->values()
            ->toArray();

        // Remove the moved task and insert at new position
        $laneTasks = array_values(array_diff($laneTasks, [$task->getKey()]));
        array_splice($laneTasks, $position, 0, [$task->getKey()]);

        // Update priorities — highest number = top of lane (DESC order)
        $total = count($laneTasks);
        foreach ($laneTasks as $index => $taskId) {
            resolve_static(Task::class, 'query')
                ->whereKey($taskId)
                ->update(['priority' => $total - $index]);
        }

        $this->islandsHaveMounted = false;
        $this->loadData(forceRender: true);

        $this->toast()
            ->success(__(':model saved', ['model' => __('Task')]))
            ->send();
    }

    public function updatedTaskTab(): void {}

    protected function availableLayouts(): array
    {
        return ['table', 'kanban'];
    }

    protected function getBuilder(Builder $builder): Builder
    {
        $builder->where('project_id', $this->projectId);

        if ($this->activeLayout === 'kanban') {
            $builder->reorder('priority', 'desc');
        }

        return $builder;
    }

    protected function isSortable(): bool
    {
        return $this->activeLayout === 'kanban';
    }

    protected function kanbanColumn(): string
    {
        return 'state';
    }

    protected function kanbanLanes(): array
    {
        $task = app(Task::class);

        return collect($this->availableStates)
            ->mapWithKeys(function (array $state) use ($task) {
                $task->state = $state['name'];

                return [
                    $state['name'] => [
                        'label' => $state['label'],
                        'color' => $task->state->color(),
                    ],
                ];
            })
            ->toArray();
    }
}
