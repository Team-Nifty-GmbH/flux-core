<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\RebateAgreement;

class RebateAgreementList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'period_start',
        'period_end',
        'settled_at',
        'is_active',
    ];

    protected string $model = RebateAgreement::class;
}
