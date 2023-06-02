<?php

namespace FluxErp\Http\Livewire\DataTables;

use FluxErp\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Helpers\ModelInfo;

class ProjectList extends DataTable
{
    protected string $model = Project::class;

    public array $enabledCols = [
        'project_name',
        'display_name',
        'release_data',
        'deadlime',
        'is_done'
    ];

    public array $availableRelations = ['*'];

    public array $sortable = ['*'];


    public function mount(): void
    {
        $attributes = ModelInfo::forModel(Project::class)->attributes;

        $this->availableCols = $attributes
            ->pluck('name')
            ->toArray();

        parent::mount();
    }
}
