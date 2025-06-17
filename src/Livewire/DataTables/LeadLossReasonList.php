<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\LeadLossReason;

class LeadLossReasonList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'is_active',
    ];

    protected string $model = LeadLossReason::class;
}
