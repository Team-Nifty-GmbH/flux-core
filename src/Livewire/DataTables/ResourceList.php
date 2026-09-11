<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Resource;

class ResourceList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'resource_number',
        'product.name',
        'is_active',
        'allow_overbooking',
    ];

    protected string $model = Resource::class;
}
