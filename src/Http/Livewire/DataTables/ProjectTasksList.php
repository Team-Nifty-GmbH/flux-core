<?php

namespace FluxErp\Http\Livewire\DataTables;

use FluxErp\Models\ProjectTask;
use Illuminate\Database\Eloquent\Builder;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Helpers\ModelInfo;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use TeamNiftyGmbH\DataTable\Traits\HasEloquentListeners;

class ProjectTasksList extends DataTable
{
    use HasEloquentListeners;

    protected string $model = ProjectTask::class;

    public bool $showFilterInputs = true;

    public array $enabledCols = [
        'id',
        'name',
        'user.user_code',
        'state',
    ];

    public array $availableRelations = ['*'];

    public array $sortable = ['*'];

    protected $listeners = [
        'refetchRecord',
    ];

    public function refetchRecord(int|array $record, string $event): void
    {
        $this->eloquentEventOccurred('echo' . $event, ['model' => $record]);
    }

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
                ->label(__('New'))
                ->color('primary')
                ->attributes([
                    'x-on:click' => "\$dispatch('new-project-task')",
                ]),
        ];
    }

    public function getBuilder($builder): Builder
    {
        return $builder->with('user:id,user_code');
    }

    public function getFilterableColumns(string $name = null): array
    {
        $filterable = parent::getFilterableColumns($name);
        $filterable[] = 'user.user_code';

        return $filterable;
    }
}
