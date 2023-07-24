<?php

namespace FluxErp\Http\Livewire\ProjectTask;

use FluxErp\Http\Requests\CreateProjectTaskRequest;
use FluxErp\Http\Requests\UpdateProjectTaskRequest;
use FluxErp\Models\Project;
use FluxErp\Services\ProjectTaskService;
use FluxErp\Traits\Livewire\HasAdditionalColumns;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use WireUi\Traits\Actions;

class ProjectTask extends Component
{
    use Actions, HasAdditionalColumns;

    public array $projectTask = [];

    public ?int $projectId = null;

    public string $tab = 'general';

    public array $availableStates = [];

    public array $categories = [];

    public array $openCategories = [];

    public function mount(?int $id = null, ?int $projectId = null): void
    {
        $this->projectId = $projectId;

        if ($id) {
            $this->showProjectTask($id);
        } else {
            $this->projectTask = [
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
        $tabs = [
            'general' => __('General'),
            'comments' => __('Comments'),
            'media' => __('Media'),
        ];

        return view('flux::livewire.project-task.project-task', ['tabs' => $tabs]);
    }

    public function showProjectTask(?int $projectTask): void
    {
        $this->resetErrorBag();

        $this->reset('tab');

        $projectTask = \FluxErp\Models\ProjectTask::query()
            ->whereKey($projectTask)
            ->with(['categories:id,parent_id', 'project'])
            ->firstOrNew();
        $project = $projectTask->project ?: Project::query()->whereKey($this->projectId)->firstOrFail();
        $projectCategories = $project
            ?->categories()
            ->get()
            ->toArray();

        $categories = to_flat_tree($project
            ->category
            ?->children()
            ->with('children:id,parent_id,name')
            ->get()
            ->toArray() ?: []
        );
        $primaryCategory = $project
            ->category
            ?->toArray();
        $this->categories = array_values(
            array_filter(
                $categories,
                fn ($category) => in_array($category['id'], array_column($projectCategories, 'id'))
            )
        );
        $this->categories[] = $primaryCategory;
        $this->categories = to_tree($this->categories);
        $this->categories = $this->categories[0]['children'];

        $this->availableStates = \FluxErp\Models\ProjectTask::getStatesFor('state')->map(function (string $state) {
            return [
                'label' => __(ucfirst(str_replace('_', ' ', $state))),
                'name' => $state,
            ];
        })->toArray();

        $this->projectTask = $projectTask->toArray();
        $this->openCategories = $projectTask->categories?->pluck('parent_id')->toArray() ?: [];

        $this->projectTask['categories'] = $projectTask->categories?->pluck('id')->first();
        $this->projectTask['user_id'] = $projectTask->user_id ?: auth()->id();
        $this->projectTask['address_id'] = $projectTask->address_id;
        unset($this->projectTask['project']);

        if (! $projectTask->exists && $this->projectId) {
            $this->projectTask['project_id'] = $this->projectId;
        }
    }

    public function save(): bool|array
    {
        $projectTask = $this->projectTask;
        $projectTask['categories'] = [$this->projectTask['categories']];

        $validator = Validator::make(
            $projectTask,
            $this->projectTask['id'] ?? false
                ? (new UpdateProjectTaskRequest())->rules()
                : (new CreateProjectTaskRequest())->rules()
        );
        $validated = $validator->validate();

        $service = new ProjectTaskService();
        if ($this->projectTask['id'] ?? false) {
            $response = $service->update($validated);
        } else {
            $response = $service->create($validated);
        }

        if ($response['errors'] ?? false) {
            foreach ($response['errors'] as $error) {
                $this->notification()->error($error);
            }

            return false;
        }

        $this->skipRender();

        return ($response['data'] ?? $response)->toArray();
    }

    public function delete(): bool
    {
        $service = new ProjectTaskService();

        $response = $service->delete($this->projectTask['id']);

        return ($response['status'] ?? false) === 204;
    }

    public function getAdditionalColumns(): array
    {
        return (new \FluxErp\Models\ProjectTask)
            ->getAdditionalColumns()
            ->toArray();
    }
}
