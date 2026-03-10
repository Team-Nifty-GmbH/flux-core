<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\States\Task\TaskState;
use FluxErp\Traits\Livewire\Widget\Widgetable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class MyTasks extends Component
{
    use Widgetable;

    public int $limit = 25;

    public static function getCategory(): ?string
    {
        return 'Tasks';
    }

    public static function dashboardComponent(): array|string
    {
        return Dashboard::class;
    }

    public static function getDefaultHeight(): int
    {
        return 2;
    }

    public static function getDefaultWidth(): int
    {
        return 2;
    }

    public function render(): View|Factory
    {
        $tasks = $this->getTasks();

        return view(
            'flux::livewire.widgets.my-tasks',
            [
                'tasks' => $tasks->take($this->limit),
                'hasMore' => $tasks->count() > $this->limit,
            ]
        );
    }

    public function loadMore(): void
    {
        $this->limit += 25;
    }

    public function placeholder(): View|Factory
    {
        return view('flux::livewire.placeholders.horizontal-bar');
    }

    protected function getTasks(): Collection
    {
        return auth()
            ->user()
            ->tasks()
            ->with(['project:id,name', 'model'])
            ->whereNotIn('state', $this->getEndStates())
            ->orderByDesc('priority')
            ->orderByRaw('ISNULL(due_date), due_date ASC')
            ->limit($this->limit + 1)
            ->get([
                'id',
                'name',
                'description',
                'state',
                'due_date',
                'due_datetime',
                'priority',
                'project_id',
                'model_type',
                'model_id',
            ]);
    }

    protected function getEndStates(): array
    {
        return TaskState::all()
            ->filter(fn (string $state): bool => $state::$isEndState)
            ->keys()
            ->toArray();
    }
}
