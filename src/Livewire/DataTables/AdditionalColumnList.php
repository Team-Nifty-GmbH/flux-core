<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\AdditionalColumn;

class AdditionalColumnList extends BaseDataTable
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
