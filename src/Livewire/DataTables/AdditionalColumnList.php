<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\AdditionalColumn;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Helpers\ModelInfo;

class AdditionalColumnList extends DataTable
{
    protected string $model = AdditionalColumn::class;

    public array $enabledCols = [
        'name',
        'model_type',
        'field_type',
        'label',
        'validations',
        'values',
    ];

    public function mount(): void
    {
        $attributes = ModelInfo::forModel($this->model)->attributes;

        $this->availableCols = $attributes
            ->pluck('name')
            ->toArray();

        parent::mount();
    }
}
