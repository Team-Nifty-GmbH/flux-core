<?php

namespace FluxErp\Livewire\Project;

use FluxErp\Actions\Project\CreateProject;
use FluxErp\Actions\Project\DeleteProject;
use FluxErp\Actions\Project\UpdateProject;
use FluxErp\Htmlables\TabButton;
use FluxErp\Models\Category;
use FluxErp\Models\ProjectTask;
use FluxErp\Traits\Livewire\HasAdditionalColumns;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;
use WireUi\Traits\Actions;

class Project extends Component
{
    use Actions, HasAdditionalColumns, WithTabs;

    public array $project = [];

    public string $tab = 'project.general';

    public array $availableStates = [];

    public array $openCategories = [];

    public array $queryString = [
        'tab' => ['except' => 'general'],
    ];

    public function mount(int $id): void
    {
        $project = \FluxErp\Models\Project::whereKey($id)
            ->with('categories:id,parent_id')
            ->withCount('tasks')
            ->firstOrFail();
        $this->availableStates = \FluxErp\Models\Project::getStatesFor('state')->map(function ($state) {
            return [
                'label' => __(ucfirst(str_replace('_', ' ', $state))),
                'name' => $state,
            ];
        })->toArray();

        $this->project = $project->toArray();
        $this->project['categories'] = $project->categories?->pluck('id')->toArray() ?: [];

        $this->openCategories = $project->categories?->pluck('parent_id')->toArray() ?: [];
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
        $action = ($this->project['id'] ?? false) ? UpdateProject::class : CreateProject::class;

        try {
            $response = $action::make($this->project)
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->notification()->success(__('Project task saved'));
        $this->skipRender();

        if ($action === CreateProject::class) {
            return $response->toArray();
        }

        return true;
    }

    public function delete(): false|Redirector
    {
        $this->skipRender();
        try {
            DeleteProject::make($this->project)
                ->checkPermission()
                ->validate()
                ->execute();

            return redirect()->route('projects.projects');
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);
        }

        return false;
    }

    public function getAdditionalColumns(): array
    {
        return (new \FluxErp\Models\Project)
            ->getAdditionalColumns()
            ->toArray();
    }

    public function loadCategories(int $categoryId = null): array
    {
        $categories = Category::query()
            ->whereKey($categoryId)
            ->with('children:id,name,parent_id')
            ->get(['id', 'name', 'parent_id'])
            ->toArray();

        $this->skipRender();

        return $categories[0]['children'] ?? [];
    }

    public function loadProjectTaskCategories(int $projectId = null): array
    {
        $tasks = ProjectTask::query()
            ->where('project_id', $projectId)
            ->with('categories:id')
            ->get()
            ->pluck('categories');

        return $tasks->flatten()->pluck('id')->toArray();
    }
}
