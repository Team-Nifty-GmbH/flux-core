<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\AbsenceRequest;

class AbsenceRequestList extends BaseDataTable
{
    public array $enabledCols = [
        'user.name',
        'work_time_category.name',
        'start_date',
        'end_date',
        'days_requested',
        'status',
        'substitute_user.name',
        'approved_by_user.name',
        'approved_at',
        'is_emergency',
    ];
    
    public array $formatters = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
    ];
    
    protected string $model = AbsenceRequest::class;
}