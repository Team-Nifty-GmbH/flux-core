<?php

namespace FluxErp\Livewire\Widgets;

use Carbon\CarbonInterval;
use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\HumanResources\WorkTimes;
use FluxErp\Livewire\Support\Widgets\ValueBox;
use FluxErp\Models\WorkTime;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Renderless;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;

class TotalUnassignedBillableHours extends ValueBox implements HasWidgetOptions
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

    #[Renderless]
    public function options(): array
    {
        return [
            [
                'label' => __('Show'),
                'method' => 'show',
            ],
        ];
    }

    #[Renderless]
    public function show(): void
    {
        SessionFilter::make(
            Livewire::new(resolve_static(WorkTimes::class, 'class'))->getCacheKey(),
            fn (Builder $query) => $query
                ->whereNull('order_position_id')
                ->where('is_billable', true)
                ->where('is_daily_work_time', false)
                ->where('total_time_ms', '>', 0),
            __(static::getLabel()),
        )
            ->store();

        $this->redirectRoute('human-resources.work-times', navigate: true);
    }

    protected function icon(): string
    {
        return 'clock';
    }
}
