<?php

namespace FluxErp\Http\Livewire\Project;

use FluxErp\Http\Requests\CreateProjectRequest;
use FluxErp\Http\Requests\UpdateProjectRequest;
use FluxErp\Models\Category;
use FluxErp\Models\ProjectTask;
use FluxErp\Services\ProjectService;
use FluxErp\Traits\Livewire\HasAdditionalColumns;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
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

    public function save(): bool|array
    {
        $validator = Validator::make(
            $this->project,
            ($this->project['id'] ?? false)
                ? (new UpdateProjectRequest())->getRules($this->project)
                : (new CreateProjectRequest())->getRules($this->project)
        );
        $validated = $validator->validate();

        $service = new ProjectService();
        if ($this->project['id'] ?? false) {
            $response = $service->update($validated);
        } else {
            $response = $service->create($validated);

            return $response['data']->toArray();
        }

        if ($response instanceof \FluxErp\Models\Project || $response['status'] === 201) {
            $this->notification()->success(__('Project task saved'));
        } else {
            $this->notification()->error(__('Project task could not be saved'));
        }

        $this->skipRender();

        return true;
    }

    public function delete(): bool
    {
        $service = new ProjectService();

        $response = $service->delete($this->project['id']);

        return ($response['status'] ?? false) === 204;
    }

    public function getAdditionalColumns(): array
    {
        return (new \FluxErp\Models\Project)
            ->getAdditionalColumns()
            ->toArray();
    }

    public function loadCategories(?int $categoryId = null): array
    {
        $categories = Category::query()
            ->whereKey($categoryId)
            ->with('children:id,name,parent_id')
            ->get(['id', 'name', 'parent_id'])
            ->toArray();

        $this->skipRender();

        return $categories[0]['children'] ?? [];
    }

    public function loadProjectTaskCategories(?int $projectId = null): array
    {
        $tasks = ProjectTask::query()
            ->where('project_id', $projectId)
            ->with('categories:id')
            ->get()
            ->pluck('categories');

        return $tasks->flatten()->pluck('id')->toArray();
    }
}
