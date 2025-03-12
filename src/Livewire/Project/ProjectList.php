<?php

namespace FluxErp\Livewire\Project;

use FluxErp\Actions\Project\CreateProject;
use FluxErp\Livewire\DataTables\ProjectList as BaseProjectList;
use FluxErp\Livewire\Forms\ProjectForm;
use FluxErp\Models\Project;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class ProjectList extends BaseProjectList
{
    public array $availableStates = [];

    public ProjectForm $project;

    protected ?string $includeBefore = 'flux::livewire.project.project-list';

    public function mount(): void
    {
        parent::mount();

        $this->project->additionalColumns = array_fill_keys(
            resolve_static(Project::class, 'additionalColumnsQuery')->pluck('name')?->toArray() ?? [],
            null
        );

        $this->availableStates = app(Project::class)
            ->getStatesFor('state')
            ->map(function (string $state) {
                return [
                    'label' => __(Str::headline($state)),
                    'name' => $state,
                ];
            })
            ->toArray();
    }

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->color('indigo')
                ->text(__('Create'))
                ->icon('plus')
                ->wireClick('createProject')
                ->when(fn () => resolve_static(CreateProject::class, 'canPerformAction', [false])),
        ];
    }

    #[Renderless]
    public function createProject(): void
    {
        $this->project->reset();
        $this->project->additionalColumns = array_fill_keys(
            resolve_static(Project::class, 'additionalColumnsQuery')
                ->pluck('name')
                ?->toArray() ?? [],
            null
        );

        $this->js(<<<'JS'
            $modalOpen('edit-project');
        JS);
    }

    #[Renderless]
    public function save(): bool
    {
        try {
            $this->project->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }
}
