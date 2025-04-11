<?php

namespace FluxErp\Livewire\Widgets;

use Carbon\CarbonInterval;
use FluxErp\Models\WorkTime;
use FluxErp\Support\Widgets\ValueBox;
use Livewire\Attributes\Renderless;

class TotalUnassignedBillableHours extends ValueBox
{
    public bool $shouldBePositive = true;

    #[Renderless]
    public function calculateSum(): void
    {
        $ms = resolve_static(WorkTime::class, 'query')
            ->whereNull('order_position_id')
            ->where('is_billable', true)
            ->where('is_daily_work_time', false)
            ->sum('total_time_ms');

        $interval = CarbonInterval::milliseconds($ms)->cascade();
        $totalHours = floor($interval->totalHours);
        $minutes = $interval->minutes;

        $this->sum = $totalHours . ' h ' . $minutes . ' min';
    }

    protected function icon(): string
    {
        return 'clock';
    }
}
