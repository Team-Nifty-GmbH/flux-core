<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\EmployeeBalanceAdjustment;

class EmployeeBalanceAdjustmentList extends BaseDataTable
{
    public array $enabledCols = [
        'employee.name',
        'type',
        'amount',
        'effective_date',
        'reason',
        'created_by',
        'created_at',
    ];

    protected string $model = EmployeeBalanceAdjustment::class;
}
