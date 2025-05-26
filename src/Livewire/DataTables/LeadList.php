<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Lead;

class LeadList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'lead_state.name',
        'address.name',
        'user.name',
        'probability_percentage',
        'score',
        'start',
        'end',
    ];

    public array $formatters = [
        'probability_percentage' => 'progressPercentage',
        'expected_gross_profit_percentage' => 'progressPercentage',
    ];

    protected string $model = Lead::class;
}
