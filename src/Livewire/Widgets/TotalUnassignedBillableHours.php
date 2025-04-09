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

        $this->sum = CarbonInterval::milliseconds($ms)->cascade()->forHumans([
            'parts' => 2,
            'join' => true,
            'short' => true,
            'locale' => 'en',
        ]);
    }
}
