<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\ProductOptionGroup;

class ProductOptionGroupList extends BaseDataTable
{
    protected string $model = ProductOptionGroup::class;

    public array $enabledCols = [
        'name',
        'product_options.name',
    ];
}
