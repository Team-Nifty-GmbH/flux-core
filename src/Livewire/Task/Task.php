<?php

namespace FluxErp\Livewire\Task;

use FluxErp\Actions\Tag\CreateTag;
use FluxErp\Actions\Task\DeleteTask;
use FluxErp\Htmlables\TabButton;
use FluxErp\Livewire\Forms\TaskForm;
use FluxErp\Models\Category;
use FluxErp\Models\Task as TaskModel;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;
use WireUi\Traits\Actions;

class Task extends Component
{
    use Actions, WithTabs;

    public TaskForm $task;

    public string $taskTab = 'task.general';

    public array $availableStates = [];

    public array $categories = [];

    public function mount(string $id): void
    {
        $task = resolve_static(TaskModel::class, 'query')
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

        $this->availableStates = app(TaskModel::class)
            ->getStatesFor('state')
            ->map(function ($state) {
                return [
                    'label' => __(ucfirst(str_replace('_', ' ', $state))),
                    'name' => $state,
                ];
            })
            ->toArray();

        $this->categories = resolve_static(Category::class, 'query')
            ->where('model_type', morph_alias(TaskModel::class))
            ->pluck('name', 'id')
            ->toArray();
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.task.task');
    }

    public function getTabs(): array
    {
        return [
            TabButton::make('task.general')->label(__('General')),
            TabButton::make('task.comments')->label(__('Comments'))
                ->attributes([
                    'x-bind:disabled' => '! $wire.task.id',
                ]),
            TabButton::make('task.media')->label(__('Media'))
                ->attributes([
                    'x-bind:disabled' => '! $wire.task.id',
                ]),
        ];
    }

    public function save(): array|bool
    {
        try {
            $this->task->save();
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->notification()->success(__('Task saved'));
        $this->skipRender();

        return true;
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

    #[Renderless]
    public function delete(): void
    {
        try {
            DeleteTask::make($this->task->toArray())
                ->checkPermission()
                ->validate()
                ->execute();

            $this->redirect(route('tasks'));
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);
        }
    }

    #[Renderless]
    public function addTag(string $name): void
    {
        try {
            $tag = CreateTag::make([
                'name' => $name,
                'type' => app(TaskModel::class)->getMorphClass(),
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
}
