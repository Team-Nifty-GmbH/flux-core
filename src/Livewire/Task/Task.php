<?php

namespace FluxErp\Livewire\Task;

use FluxErp\Actions\Task\DeleteTask;
use FluxErp\Htmlables\TabButton;
use FluxErp\Livewire\Forms\TaskForm;
use FluxErp\Models\Task as TaskModel;
use FluxErp\Traits\Livewire\HasAdditionalColumns;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use WireUi\Traits\Actions;

class Task extends Component
{
    use Actions, WithTabs;

    public TaskForm $task;

    public string $taskTab = 'task.general';

    public array $availableStates = [];

    public function mount(string $id): void
    {
        $task = TaskModel::query()
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

        $this->availableStates = TaskModel::getStatesFor('state')->map(function ($state) {
            return [
                'label' => __(ucfirst(str_replace('_', ' ', $state))),
                'name' => $state,
            ];
        })->toArray();
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
        $task = TaskModel::query()
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

    public function delete(): void
    {
        $this->skipRender();
        try {
            DeleteTask::make($this->task)
                ->checkPermission()
                ->validate()
                ->execute();

            $this->redirect(route('tasks'));
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);
        }
    }
}
