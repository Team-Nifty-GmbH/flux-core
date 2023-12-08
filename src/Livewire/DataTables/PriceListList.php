<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\PriceList;
use TeamNiftyGmbH\DataTable\DataTable;

class PriceListList extends DataTable
{
    protected string $model = PriceList::class;

    public array $enabledCols = [
        'name',
        'price_list_code',
        'is_net',
        'is_default',
    ];
}
