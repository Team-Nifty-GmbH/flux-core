<?php

namespace FluxErp\Livewire\Widgets;

use Carbon\CarbonInterval;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Support\Widgets\ValueBox;
use FluxErp\Models\WorkTime;
use Livewire\Attributes\Renderless;

class TotalUnassignedBillableHours extends ValueBox
{
    public bool $shouldBePositive = true;

    public static function dashboardComponent(): array|string
    {
        return Dashboard::class;
    }

    #[Renderless]
    public function calculateSum(): void
    {
        $ms = resolve_static(WorkTime::class, 'query')
            ->whereNull('order_position_id')
            ->where('is_billable', true)
            ->where('is_daily_work_time', false)
            ->sum('total_time_ms');

        $interval = CarbonInterval::milliseconds($ms)->cascade();

        $totalHours = (int) $interval->totalHours;
        $minutes = $interval->minutes;

        $this->sum = __('time.hours_minutes', [
            'hours' => $totalHours,
            'minutes' => $minutes,
        ]);
    }

    protected function icon(): string
    {
        return 'clock';
    }
}
