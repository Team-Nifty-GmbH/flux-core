<?php

namespace FluxErp\Http\Livewire\DataTables;

use FluxErp\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Helpers\ModelInfo;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use TeamNiftyGmbH\DataTable\Traits\HasEloquentListeners;

class ProjectList extends DataTable
{
    use HasEloquentListeners;

    protected string $model = Project::class;

    public bool $showFilterInputs = true;

    public array $enabledCols = [
        'id',
        'state',
        'project_name',
        'deadline',
        'release_date',
        'category.name',
    ];

    public array $sortable = ['*'];

    public array $availableRelations = ['*'];

    public function mount(): void
    {
        $attributes = ModelInfo::forModel($this->model)->attributes;

        $this->availableCols = $attributes
            ->pluck('name')
            ->toArray();

        parent::mount();
    }

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->color('primary')
                ->label(__('New project'))
                ->icon('plus')
                ->attributes([
                    'x-on:click' => "\$dispatch('create-project')",
                ]),
        ];
    }

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->with('category:id,name');
    }

    public function getFilterableColumns(?string $name = null): array
    {
        $filterable = parent::getFilterableColumns($name);
        $filterable[] = 'category.name';

        return $filterable;
    }
}
