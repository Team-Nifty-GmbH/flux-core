<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Support\Widgets\Charts\BarChart;
use FluxErp\Models\WorkTime;
use FluxErp\Models\WorkTimeType;
use FluxErp\Support\Metrics\Charts\Bar;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use Livewire\Attributes\Js;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;

class MyWorkTimes extends BarChart
{
    use IsTimeFrameAwareWidget;

    public ?array $chart = [
        'type' => 'bar',
        'stacked' => true,
    ];

    public ?array $dataLabels = [
        'enabled' => true,
    ];

    public ?array $plotOptions = [
        'bar' => [
            'horizontal' => true,
            'endingShape' => 'rounded',
            'columnWidth' => '55%',
        ],
    ];

    public bool $showTotals = true;

    #[Locked]
    public ?int $userId = null;

    public static function dashboardComponent(): array|string
    {
        return Dashboard::class;
    }

    public function mount(): void
    {
        $this->userId = $this->userId ?? auth()->id();

        parent::mount();
    }

    #[Renderless]
    public function calculateByTimeFrame(): void
    {
        $this->calculateChart();
        $this->updateData();
    }

    public function calculateChart(): void
    {
        $this->xaxis = null;

        $baseQuery = resolve_static(WorkTime::class, 'query')
            ->where('user_id', $this->userId)
            ->where('is_locked', true)
            ->whereBetween('started_at', [$this->getStart(), $this->getEnd()]);

        $workDays = Bar::make(
            $baseQuery
                ->clone()
                ->where('is_daily_work_time', true)
                ->where('is_pause', false)
        )
            ->setDateColumn('started_at')
            ->setRange($this->timeFrame)
            ->setEndingDate($this->getEnd())
            ->setStartingDate($this->getStart())
            ->sum('total_time_ms');

        $pause = Bar::make(
            $baseQuery
                ->clone()
                ->where('is_daily_work_time', true)
                ->where('is_pause', true)
        )
            ->setDateColumn('started_at')
            ->setRange($this->timeFrame)
            ->setEndingDate($this->getEnd())
            ->setStartingDate($this->getStart())
            ->sum('total_time_ms');

        $data = [
            'presence' => [
                'name' => __('Presence'),
                'group' => 'worktime',
                'color' => 'indigo',
                'data' => $workDays->getData(),
                'growthRate' => $workDays->getGrowthRate(),
            ],
            'pause_time' => [
                'name' => __('Paused Time'),
                'group' => 'worktime',
                'color' => 'amber',
                'data' => $pause->getData(),
                'growthRate' => $pause->getGrowthRate(),
            ],
            'work_time' => [
                'name' => __('Work Time'),
                'group' => 'total_worktime',
                'color' => 'pink',
                'data' => array_map(
                    fn ($workTime, $pause) => bcadd($workTime, $pause),
                    $workDays->getData(),
                    $pause->getData()
                ),
            ],
        ];

        $colors = [
            'lime-400',
            'sky-400',
            'violet-400',
            'cyan-400',
            'blue-400',
            'purple-400',
            'green-400',
            'indigo-400',
            'fuchsia-400',
            'emerald-400',
            'pink-400',
            'teal-400',
        ];

        foreach (WorkTimeType::query()->pluck('name', 'id') as $workTimeTypeID => $name) {
            $typeData = Bar::make(
                $baseQuery
                    ->clone()
                    ->where('is_daily_work_time', false)
                    ->where('work_time_type_id', $workTimeTypeID)
            )
                ->setDateColumn('started_at')
                ->setRange($this->timeFrame)
                ->setEndingDate($this->getEnd())
                ->setStartingDate($this->getStart())
                ->sum('total_time_ms');

            if (array_sum($typeData->getData()) > 0) {
                $data['task_time_' . $workTimeTypeID] = [
                    'name' => $name ?? __('Unknown'),
                    'group' => 'tasktime',
                    'color' => array_shift($colors),
                    'data' => $typeData->getData(),
                    'growthRate' => $typeData->getGrowthRate(),
                ];
            }
        }

        $this->xaxis = [
            'categories' => array_unique(array_merge($workDays->getLabels(), $pause->getLabels())),
        ];

        $this->series = array_values($data);
    }

    #[Js]
    public function dataLabelsFormatter(): string
    {
        return <<<'JS'
            if (val > 3600000) {
                let hours = val / 3600000;
                return hours.toFixed(2) + 'h';
            } else {
                let minutes = val / 60000;
                return minutes.toFixed(0) + 'm';
            }
        JS;
    }

    public function showTitle(): bool
    {
        return false;
    }

    #[Js]
    public function toolTipFormatter(): string
    {
        return <<<'JS'
            let hours = val / 3600000;
            return hours.toFixed(2) + 'h';
        JS;
    }

    #[Js]
    public function xAxisFormatter(): string
    {
        return $this->toolTipFormatter();
    }

    #[Js]
    public function yAxisFormatter(): string
    {
        return <<<'JS'
            let name;
            if (typeof val === 'string' && val.includes('->')) {
                name = val.split('->')[1];
                val = val.split('->')[0];
            }

            return new Date(val).toLocaleDateString(document.documentElement.lang) + (name ? ' (' + name + ')' : '')
        JS;
    }
}
