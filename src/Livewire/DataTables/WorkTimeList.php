<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\WorkTime;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class WorkTimeList extends DataTable
{
    protected string $model = WorkTime::class;

    public array $enabledCols = [
        'user.name',
        'name',
        'total_time',
        'paused_time',
        'started_at',
        'ended_at',
        'is_locked',
    ];

    public array $formatters = [
        'total_time' => 'time',
        'paused_time' => 'time',
    ];

    public bool $isSelectable = true;

    public function itemToArray($item): array
    {
        $item = parent::itemToArray($item);
        $item['total_time'] = ($item['total_time'] ?? 0) * 1000;
        $item['paused_time'] = ($item['paused_time'] ?? 0) * 1000;
        $item['name'] = __($item['name']);

        return $item;
    }
}
