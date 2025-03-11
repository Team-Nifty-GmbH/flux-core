<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\PriceList;

class PriceListList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'price_list_code',
        'is_net',
        'is_default',
    ];

    protected string $model = PriceList::class;
}
