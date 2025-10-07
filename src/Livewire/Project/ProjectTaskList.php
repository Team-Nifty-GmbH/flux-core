<?php

namespace FluxErp\Livewire\Project;

use FluxErp\Htmlables\TabButton;
use FluxErp\Livewire\DataTables\TaskList as BaseTaskList;
use FluxErp\Livewire\Forms\TaskForm;
use FluxErp\Models\Task;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\DataTableHasFormEdit;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Livewire\Attributes\Renderless;

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
        $this->task->additionalColumns = array_fill_keys(
            resolve_static(Task::class, 'additionalColumnsQuery')->pluck('name')?->toArray() ?? [],
            null
        );

        $this->availableStates = app(Task::class)->getStatesFor('state')
            ->map(function (string $state) {
                return [
                    'label' => __(Str::headline($state)),
                    'name' => $state,
                ];
            })
            ->toArray();
    }

    #[Renderless]
    public function edit(string|int|null $id = null): void
    {
        if ($id) {
            $task = resolve_static(Task::class, 'query')
                ->whereKey($id)
                ->where('project_id', $this->projectId)
                ->firstOrFail();
        } else {
            $task = app(Task::class);
        }

        $this->reset('taskTab');
        $this->editForm($id);

        $this->task->project_id = $this->projectId;

        $this->task->additionalColumns = array_intersect_key(
            $task->toArray(),
            array_fill_keys(
                $task->additionalColumns()->pluck('name')?->toArray() ?? [],
                null
            )
        );
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

    public function updatedTaskTab(): void
    {
        $this->forceRender();
    }

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->where('project_id', $this->projectId);
    }
}
