<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\ProductPropertyGroup;

class ProductPropertyGroupList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'product_properties.name',
    ];

    protected string $model = ProductPropertyGroup::class;
}
