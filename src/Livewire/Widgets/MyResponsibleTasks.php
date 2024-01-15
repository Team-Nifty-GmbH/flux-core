<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\States\Task\TaskState;
use FluxErp\Traits\Widgetable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class MyResponsibleTasks extends Component
{
    use Widgetable;

    public function render(): View|Factory
    {
        $endStates = TaskState::all()->filter(fn ($state) => $state::$isEndState)->keys()->toArray();

        return view(
            'flux::livewire.widgets.my-responsible-tasks',
            [
                'tasks' => auth()
                    ->user()
                    ->tasksResponsible()
                    ->whereNotIn('state', $endStates)
                    ->orderByDesc('priority')
                    ->with('users:id,name')
                    ->orderByRaw('ISNULL(due_date), due_date ASC')
                    ->get(),
            ]
        );
    }

    public function placeholder(): View|Factory
    {
        return view('flux::livewire.placeholders.horizontal-bar');
    }
}
