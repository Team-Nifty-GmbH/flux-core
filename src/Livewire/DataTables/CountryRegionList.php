<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\CountryRegion;

class CountryRegionList extends BaseDataTable
{
    public array $enabledCols = [
        'id',
        'country.name',
        'name',
    ];

    protected string $model = CountryRegion::class;
}
