<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\VacationCarryoverRule;

class VacationCarryOverRuleList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'max_days',
        'expires_at_month',
        'expires_at_day',
        'is_active',
        'is_default',
    ];

    protected string $model = VacationCarryoverRule::class;
}
