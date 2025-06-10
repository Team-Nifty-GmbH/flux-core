<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\LeadState;

class LeadStateList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'probability_percentage',
    ];

    protected string $model = LeadState::class;
}
