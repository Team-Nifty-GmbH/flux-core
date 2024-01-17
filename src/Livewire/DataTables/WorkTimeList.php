<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\WorkTime;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use TeamNiftyGmbH\DataTable\Traits\HasEloquentListeners;

class WorkTimeList extends DataTable
{
    use HasEloquentListeners;

    protected string $model = WorkTime::class;

    protected ?string $includeBefore = 'flux::livewire.datatables.work-time-list.include-before';

    public array $enabledCols = [
        'user.name',
        'name',
        'total_time_ms',
        'paused_time_ms',
        'started_at',
        'ended_at',
        'is_locked',
        'is_daily_work_time',
    ];

    public array $formatters = [
        'total_time_ms' => 'time',
        'paused_time_ms' => 'time',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public bool $isSelectable = true;

    public function itemToArray($item): array
    {
        $item = parent::itemToArray($item);
        $item['name'] = __($item['name']);

        return $item;
    }

    public function getAggregatable(): array
    {
        return array_merge(parent::getAggregatable(), ['paused_time_ms', 'total_time_ms']);
    }

    public function getSelectedActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Create Orders'))
                ->color('primary')
                ->wireClick('createOrders'),
        ];
    }

    public function createOrders()
    {
        dd($this->selected);
        $this->js(<<<'JS'
            $openModal('create-orders');
        JS);
    }
}
