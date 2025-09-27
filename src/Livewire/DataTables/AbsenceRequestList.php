<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\AbsenceRequest;

class AbsenceRequestList extends BaseDataTable
{
    public array $enabledCols = [
        'employee.name',
        'absence_type.name',
        'start_date',
        'end_date',
        'days_requested',
        'state_enum',
        'substitutes.name',
        'is_emergency',
    ];

    public bool $hasNoRedirect = true;

    protected string $model = AbsenceRequest::class;
}
