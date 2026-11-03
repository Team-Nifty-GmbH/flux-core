<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\SalaryComponent;

class SalaryComponentList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'amount',
        'payment_interval',
        'valid_from',
        'valid_until',
    ];

    public array $formatters = [
        'amount' => 'money',
    ];

    protected string $model = SalaryComponent::class;
}
