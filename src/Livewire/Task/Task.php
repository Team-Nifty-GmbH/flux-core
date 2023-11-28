<?php

namespace FluxErp\Livewire\Task;

use FluxErp\Actions\Task\CreateTask;
use FluxErp\Actions\Task\DeleteTask;
use FluxErp\Actions\Task\UpdateTask;
use FluxErp\Htmlables\TabButton;
use FluxErp\Traits\Livewire\HasAdditionalColumns;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use WireUi\Traits\Actions;

class Task extends Component
{
    use Actions, HasAdditionalColumns, WithTabs;

    public array $task = [];

    public ?int $projectId = null;

    public string $taskTab = 'task.general';

    public array $availableStates = [];

    public array $categories = [];

    public array $openCategories = [];

    public function mount(int $id = null, int $projectId = null): void
    {
        $this->projectId = $projectId;

        if ($id) {
            $this->showTask($id);
        } else {
            $this->task = [
                'id' => 0,
                'address_id' => null,
                'project_id' => $projectId,
                'user_id' => auth()->id(),
                'state' => 'open',
                'categories' => [],
            ];
        }
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

    public function showTask(?int $task): void
    {
        $this->resetErrorBag();

        $this->reset('taskTab');

        $task = \FluxErp\Models\Task::query()
            ->whereKey($task)
            ->with(['categories:id,parent_id', 'project'])
            ->firstOrNew();

        $this->availableStates = \FluxErp\Models\Task::getStatesFor('state')->map(function (string $state) {
            return [
                'label' => __(ucfirst(str_replace('_', ' ', $state))),
                'name' => $state,
            ];
        })->toArray();

        $this->task = $task->toArray();
        $this->openCategories = $task->categories?->pluck('parent_id')->toArray() ?: [];

        $this->task['categories'] = $task->categories?->pluck('id')->first();
        $this->task['user_id'] = $task->user_id ?: auth()->id();
        $this->task['address_id'] = $task->address_id;
        unset($this->task['project']);

        if (! $task->exists && $this->projectId) {
            $this->task['project_id'] = $this->projectId;
        }
    }

    public function save(): false|array
    {
        $task = $this->task;
        $task['categories'] = array_map('intval', [$this->task['categories']]);
        unset($task['category_id']);
        $action = ($this->task['id'] ?? false) ? UpdateTask::class : CreateTask::class;

        try {
            $response = $action::make($task)
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->notification()->success(__('Project task saved'));
        $this->skipRender();

        return $response->toArray();
    }

    public function delete(): bool
    {
        try {
            DeleteTask::make($this->task)
                ->checkPermission()
                ->validate()
                ->execute();

        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        return true;
    }

    public function getAdditionalColumns(): array
    {
        return (new \FluxErp\Models\Task)
            ->getAdditionalColumns()
            ->toArray();
    }
}
