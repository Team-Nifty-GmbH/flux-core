<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Location;

class LocationList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'street',
        'city',
        'zip',
        'country.name',
        'is_active',
    ];

    protected string $model = Location::class;
}
