<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\CommissionRate;

class CommissionRateList extends BaseDataTable
{
    public array $enabledCols = [
        'category.name',
        'product.name',
        'commission_rate',
    ];

    protected string $model = CommissionRate::class;
}
