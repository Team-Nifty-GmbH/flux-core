<?php

namespace FluxErp\Livewire\Project;

use FluxErp\States\Project\Open;
use Illuminate\Contracts\View\View;

class ProjectList extends Project
{
    public function mount(int $id = null): void
    {
        $this->availableStates = \FluxErp\Models\Project::getStatesFor('state')->map(function ($state) {
            return [
                'label' => __(ucfirst(str_replace('_', ' ', $state))),
                'name' => $state,
            ];
        })->toArray();

        $this->project['state'] = Open::$name;
        $this->project['categories'] = [];
        $this->project['category_id'] = null;
    }

    public function render(): View
    {
        return view('flux::livewire.project.project-list');
    }
}
