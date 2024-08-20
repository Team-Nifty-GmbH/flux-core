<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\ProductPropertyGroup;

class ProductPropertyGroupList extends BaseDataTable
{
    protected string $model = ProductPropertyGroup::class;

    public array $enabledCols = [
        'name',
        'product_properties.name',
    ];
}
