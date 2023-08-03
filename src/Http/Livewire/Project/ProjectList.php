<?php

namespace FluxErp\Http\Livewire\Project;

use FluxErp\States\Project\Open;

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

    public function render(): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\Foundation\Application
    {
        return view('flux::livewire.project.project-list');
    }
}
