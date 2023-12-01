<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\AdditionalColumn;
use TeamNiftyGmbH\DataTable\DataTable;

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
}
