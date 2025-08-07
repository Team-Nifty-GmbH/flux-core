<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\VacationCarryoverRule;

class VacationCarryoverRuleList extends BaseDataTable
{
    public array $enabledCols = [
        'effective_year',
        'cutoff_month',
        'cutoff_day',
        'max_carryover_days',
        'expiry_date',
        'is_active',
    ];

    protected string $model = VacationCarryoverRule::class;
}