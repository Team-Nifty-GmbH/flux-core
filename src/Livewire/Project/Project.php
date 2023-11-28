<?php

namespace FluxErp\Livewire\Project;

use FluxErp\Actions\Project\CreateProject;
use FluxErp\Actions\Project\DeleteProject;
use FluxErp\Actions\Project\UpdateProject;
use FluxErp\Htmlables\TabButton;
use FluxErp\Livewire\Forms\ProjectForm;
use FluxErp\Models\Category;
use FluxErp\Models\Task;
use FluxErp\Traits\Livewire\HasAdditionalColumns;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use WireUi\Traits\Actions;

class Project extends Component
{
    use Actions, HasAdditionalColumns, WithTabs;

    public ProjectForm $project;

    public string $tab = 'project.general';

    public array $availableStates = [];

    public array $openCategories = [];

    public array $queryString = [
        'tab' => ['except' => 'general'],
    ];

    public function mount(int $id): void
    {
        $project = \FluxErp\Models\Project::whereKey($id)
            ->withCount('tasks')
            ->firstOrFail();
        $this->project->fill($project);

        $this->availableStates = \FluxErp\Models\Project::getStatesFor('state')->map(function ($state) {
            return [
                'label' => __(ucfirst(str_replace('_', ' ', $state))),
                'name' => $state,
            ];
        })->toArray();
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.project.project');
    }

    public function getTabs(): array
    {
        return [
            TabButton::make('project.general')->label(__('General')),
            TabButton::make('project.comments')->label(__('Comments')),
            TabButton::make('project.statistics')->label(__('Statistics')),
        ];
    }

    public function save(): array|bool
    {
        try {
            $this->project->save();
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->notification()->success(__('Project task saved'));
        $this->skipRender();

        return true;
    }

    public function delete(): void
    {
        $this->skipRender();
        try {
            DeleteProject::make($this->project)
                ->checkPermission()
                ->validate()
                ->execute();

            $this->redirect(route('projects'));
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);
        }
    }

    public function getAdditionalColumns(): array
    {
        return (new \FluxErp\Models\Project)
            ->getAdditionalColumns()
            ->toArray();
    }

    #[Computed]
    public function avatarUrl(): ?string
    {
        return $this->project->id
            ? \FluxErp\Models\Project::query()->whereKey($this->project->id)->first()->getAvatarUrl()
            : null;
    }
}
