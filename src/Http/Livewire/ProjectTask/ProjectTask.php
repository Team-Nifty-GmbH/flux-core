<?php

namespace FluxErp\Http\Livewire\ProjectTask;

use FluxErp\Actions\ProjectTask\CreateProjectTask;
use FluxErp\Actions\ProjectTask\DeleteProjectTask;
use FluxErp\Actions\ProjectTask\UpdateProjectTask;
use FluxErp\Models\Project;
use FluxErp\Traits\Livewire\HasAdditionalColumns;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
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

    public function mount(int $id = null, int $projectId = null): void
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
        $this->categories = $this->categories[0]['children'] ?? [];

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

    public function save(): false|array
    {
        $projectTask = $this->projectTask;
        $projectTask['categories'] = array_map('intval', [$this->projectTask['categories']]);
        unset($projectTask['category_id']);
        $action = ($this->projectTask['id'] ?? false) ? UpdateProjectTask::class : CreateProjectTask::class;

        try {
            $response = $action::make($projectTask)
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->notification()->success(__('Project task saved'));
        $this->skipRender();

        return $response->toArray();
    }

    public function delete(): bool
    {
        try {
            DeleteProjectTask::make($this->projectTask)
                ->checkPermission()
                ->validate()
                ->execute();

        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        return true;
    }

    public function getAdditionalColumns(): array
    {
        return (new \FluxErp\Models\ProjectTask)
            ->getAdditionalColumns()
            ->toArray();
    }
}
