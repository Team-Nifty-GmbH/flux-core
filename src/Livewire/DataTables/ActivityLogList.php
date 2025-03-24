<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Activity;

class ActivityLogList extends BaseDataTable
{
    public array $enabledCols = [
        'causer_type',
        'causer_id',
        'subject_type',
        'subject_id',
        'event',
        'description',
        'created_at',
    ];

    protected string $model = Activity::class;
}
