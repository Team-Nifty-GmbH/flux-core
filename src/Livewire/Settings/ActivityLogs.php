<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\DataTables\ActivityLogList;
use FluxErp\Models\Activity;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class ActivityLogs extends ActivityLogList
{
    protected ?string $includeBefore = 'flux::livewire.settings.activity-logs';

    public array $activity = [];

    public function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->icon('eye')
                ->label(__('Show'))
                ->color('primary')
                ->wireClick('show(record.id)'),
        ];
    }

    public function show(Activity $activity): void
    {
        $this->activity = $activity->toArray();
        $this->activity['causer'] = $activity->causer?->name;

        $this->js(<<<'JS'
            $openModal('activity-log-detail');
        JS);
    }
}
