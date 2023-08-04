<?php

namespace FluxErp\Http\Livewire\Project;

use FluxErp\Actions\Project\CreateProject;
use FluxErp\Actions\Project\DeleteProject;
use FluxErp\Actions\Project\UpdateProject;
use FluxErp\Models\Category;
use FluxErp\Models\ProjectTask;
use FluxErp\Traits\Livewire\HasAdditionalColumns;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Livewire\Component;
use Livewire\Redirector;
use WireUi\Traits\Actions;

class Project extends Component
{
    use Actions, HasAdditionalColumns;

    public array $project = [];

    public string $tab = 'general';

    public array $availableStates = [];

    public array $openCategories = [];

    public $queryString = [
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
        $tabs = [
            'general' => __('General'),
            'comments' => __('Comments'),
            'statistics' => __('Statistics'),
        ];

        return view('flux::livewire.project.project', ['tabs' => $tabs]);
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

    public function delete(): false|RedirectResponse|Redirector
    {
        $this->skipRender();
        try {
            DeleteProject::make($this->project)
                ->checkPermission()
                ->validate()
                ->execute();

            return redirect()->route('projects');
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
