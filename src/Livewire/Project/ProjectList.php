<?php

namespace FluxErp\Livewire\Project;

use FluxErp\Actions\Project\CreateProject;
use FluxErp\Livewire\DataTables\ProjectList as BaseProjectList;
use FluxErp\Livewire\Forms\ProjectForm;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class ProjectList extends BaseProjectList
{
    protected string $view = 'flux::livewire.project.project-list';

    public array $availableStates = [];

    public ProjectForm $project;

    public function mount(): void
    {
        parent::mount();

        $this->availableStates = \FluxErp\Models\Project::getStatesFor('state')->map(function ($state) {
            return [
                'label' => __(ucfirst(str_replace('_', ' ', $state))),
                'name' => $state,
            ];
        })->toArray();
    }

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->color('primary')
                ->label(__('Create'))
                ->icon('plus')
                ->attributes([
                    'x-on:click' => "\$dispatch('create-project')",
                ])
                ->when(fn () => CreateProject::canPerformAction(false)),
        ];
    }

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
