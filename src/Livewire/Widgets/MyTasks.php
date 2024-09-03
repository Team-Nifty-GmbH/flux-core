<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\States\Task\TaskState;
use FluxErp\Traits\Widgetable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class MyTasks extends Component
{
    use Widgetable;

    public function render(): View|Factory
    {
        $endStates = TaskState::all()
            ->filter(fn ($state) => $state::$isEndState)
            ->keys()
            ->toArray();

        return view(
            'flux::livewire.widgets.my-tasks',
            [
                'tasks' => auth()
                    ->user()
                    ->tasks()
                    ->with('project:id,name')
                    ->whereNotIn('state', $endStates)
                    ->orderByDesc('priority')
                    ->orderByRaw('ISNULL(due_date), due_date ASC')
                    ->get(),
            ]
        );
    }

    public function placeholder(): View|Factory
    {
        return view('flux::livewire.placeholders.horizontal-bar');
    }

    public static function getDefaultWidth(): int
    {
        return 2;
    }

    public static function getDefaultHeight(): int
    {
        return 2;
    }
}
