<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\ProductConfigurationGroup;

class ProductConfigurationGroupList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'selection_type_enum',
        'price_mode_enum',
    ];

    protected string $model = ProductConfigurationGroup::class;
}
