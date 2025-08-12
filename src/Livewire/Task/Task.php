<?php

namespace FluxErp\Livewire\Task;

use Exception;
use FluxErp\Actions\Tag\CreateTag;
use FluxErp\Actions\Task\DeleteTask;
use FluxErp\Actions\Task\ReplicateTask;
use FluxErp\Htmlables\TabButton;
use FluxErp\Livewire\Forms\TaskForm;
use FluxErp\Models\Task as TaskModel;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class Task extends Component
{
    use Actions, WithTabs;

    public array $availableStates = [];

    public TaskForm $replica;

    public TaskForm $task;

    public int $taskId;

    public string $taskTab = 'task.general';

    public function mount(string $id): void
    {
        $task = resolve_static(TaskModel::class, 'query')
            ->with('model')
            ->whereKey($id)
            ->firstOrFail();

        $this->task->fill($task);
        $this->task->users = $task->users()->pluck('users.id')->toArray();
        $this->task->additionalColumns = array_intersect_key(
            $task->toArray(),
            array_fill_keys(
                $task->additionalColumns()->pluck('name')?->toArray() ?? [],
                null
            )
        );

        if ($task->model && in_array(InteractsWithDataTables::class, class_implements($task->model))) {
            $this->task->modelUrl = $task->model->getUrl();
            $this->task->modelLabel = $task->model->getLabel();
        }

        $this->availableStates = app(TaskModel::class)
            ->getStatesFor('state')
            ->map(function (string $state) {
                return [
                    'label' => __(Str::headline($state)),
                    'name' => $state,
                ];
            })
            ->toArray();

        $this->taskId = $task->id;
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.task.task');
    }

    #[Renderless]
    public function addTag(string $name): void
    {
        try {
            $tag = CreateTag::make([
                'name' => $name,
                'type' => morph_alias(TaskModel::class),
            ])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->task->tags[] = $tag->id;
        $this->js(<<<'JS'
            edit = true;
        JS);
    }

    #[Renderless]
    public function delete(): void
    {
        try {
            DeleteTask::make($this->task->toArray())
                ->checkPermission()
                ->validate()
                ->execute();

            $this->redirectRoute('tasks', navigate: true);
        } catch (Exception $e) {
            exception_to_notifications($e, $this);
        }
    }

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

    #[Renderless]
    public function replicate(): void
    {
        try {
            $replica = ReplicateTask::make($this->replica->toArray())
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->redirectRoute(name: 'tasks.id', parameters: ['id' => $replica->id], navigate: true);
    }

    public function resetForm(): void
    {
        $task = resolve_static(TaskModel::class, 'query')
            ->whereKey($this->task->id)
            ->firstOrFail();

        $this->task->reset();
        $this->task->fill($task);
        $this->task->users = $task->users()->pluck('users.id')->toArray();
        $this->task->additionalColumns = array_intersect_key(
            $task->toArray(),
            array_fill_keys(
                $task->additionalColumns()->pluck('name')?->toArray() ?? [],
                null
            )
        );
    }

    public function save(): array|bool
    {
        try {
            $this->task->save();
        } catch (Exception $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->notification()->success(__(':model saved', ['model' => __('Task')]))->send();
        $this->skipRender();

        return true;
    }

    #[Renderless]
    public function showReplicate(): void
    {
        $this->replica = $this->task;

        $this->js(<<<'JS'
            $modalOpen('replicate-task-modal');
        JS);
    }

    #[Renderless]
    public function updateReplica(TaskModel $task): void
    {
        $this->replica->reset();
        $this->replica->fill($task);
    }
}
