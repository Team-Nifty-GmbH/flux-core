<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\DataTables\ActivityLogList;
use FluxErp\Models\Activity;
use Livewire\Attributes\Renderless;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class ActivityLogs extends ActivityLogList
{
    public array $activity = [];

    protected ?string $includeBefore = 'flux::livewire.settings.activity-logs';

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->icon('eye')
                ->text(__('Show'))
                ->color('indigo')
                ->wireClick('edit(record.id)'),
        ];
    }

    #[Renderless]
    public function edit(Activity $activity): void
    {
        $this->activity = $activity->toArray();
        $this->activity['causer'] = $activity->causer?->name;

        $this->modalOpen('activity-log-detail');
    }
}
