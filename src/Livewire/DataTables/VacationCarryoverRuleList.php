<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\VacationCarryOverRule;

class VacationCarryoverRuleList extends BaseDataTable
{
    public array $enabledCols = [
        'max_days',
        'expires_after_months',
        'is_active',
    ];

    protected string $model = VacationCarryOverRule::class;
}
