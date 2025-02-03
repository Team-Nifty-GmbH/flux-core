<?php

namespace FluxErp\Livewire\Project;

use FluxErp\Actions\Project\DeleteProject;
use FluxErp\Htmlables\TabButton;
use FluxErp\Livewire\Forms\ProjectForm;
use FluxErp\Models\Project as ProjectModel;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\WithFileUploads;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Project extends Component
{
    use Actions, WithFileUploads, WithTabs;

    public ProjectForm $project;

    public string $tab = 'project.general';

    public array $availableStates = [];

    public array $openCategories = [];

    public array $queryString = [
        'tab' => ['except' => 'general'],
    ];

    public $avatar;

    public function mount(string $id): void
    {
        $project = resolve_static(ProjectModel::class, 'query')
            ->whereKey($id)
            ->with([
                'contact.media' => fn (MorphMany $query) => $query->where('collection_name', 'avatar'),
            ])
            ->withCount('tasks')
            ->firstOrFail();
        $this->project->fill($project);
        $this->project->additionalColumns = array_intersect_key(
            $project->toArray(),
            array_fill_keys(
                $project->additionalColumns()->pluck('name')?->toArray() ?? [],
                null
            )
        );
        $this->avatar = $project->getAvatarUrl();

        $this->availableStates = app(ProjectModel::class)
            ->getStatesFor('state')
            ->map(function (string $state) {
                return [
                    'label' => __(Str::headline($state)),
                    'name' => $state,
                ];
            })
            ->toArray();
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

        $this->notification()->success(__(':model saved', ['model' => __('Project')]));
        $this->skipRender();

        return true;
    }

    public function resetForm(): void
    {
        $project = resolve_static(ProjectModel::class, 'query')
            ->whereKey($this->project->id)
            ->firstOrFail();

        $this->project->reset();
        $this->project->fill($project);
        $this->project->additionalColumns = array_intersect_key(
            $project->toArray(),
            array_fill_keys(
                $project->additionalColumns()->pluck('name')?->toArray() ?? [],
                null
            )
        );
    }

    public function delete(): void
    {
        $this->skipRender();
        try {
            DeleteProject::make($this->project->toArray())
                ->checkPermission()
                ->validate()
                ->execute();

            $this->redirect(route('projects'));
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);
        }
    }

    #[Computed]
    public function avatarUrl(): ?string
    {
        return $this->project->id
            ? resolve_static(ProjectModel::class, 'query')
                ->whereKey($this->project->id)
                ->first()
                ->getAvatarUrl()
            : null;
    }

    public function updatedAvatar(): void
    {
        $this->collection = 'avatar';
        try {
            $this->saveFileUploadsToMediaLibrary(
                'avatar',
                $this->project->id,
                morph_alias(ProjectModel::class)
            );
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->avatar = resolve_static(ProjectModel::class, 'query')
            ->whereKey($this->project->id)
            ->first()
            ->getAvatarUrl();
    }
}
