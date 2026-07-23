<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Rule;

class RuleList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'description',
        'priority',
        'is_active',
    ];

    protected string $model = Rule::class;
}
