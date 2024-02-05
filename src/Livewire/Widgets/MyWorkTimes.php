<?php

namespace FluxErp\Livewire\Widgets;

use Carbon\Carbon;
use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Livewire\Charts\BarChart;
use FluxErp\Models\WorkTime;
use FluxErp\Models\WorkTimeType;
use FluxErp\Traits\Widgetable;
use Livewire\Attributes\Js;
use Livewire\Attributes\Locked;

class MyWorkTimes extends BarChart
{
    use Widgetable;

    public bool $showTotals = false;

    public ?array $plotOptions = [
        'bar' => [
            'horizontal' => true,
            'endingShape' => 'rounded',
            'columnWidth' => '55%',
        ],
    ];

    public ?array $chart = [
        'type' => 'bar',
        'stacked' => true,
    ];

    public ?array $dataLabels = [
        'enabled' => true,
    ];

    #[Locked]
    public ?int $userId = null;

    public function mount(): void
    {
        $this->userId = $this->userId ?? auth()->id();

        parent::mount();
    }

    #[Js]
    public function toolTipFormatter(): string
    {
        return <<<'JS'
            let hours = val / 60000;
            return hours.toFixed(2) + 'h';
        JS;
    }

    #[Js]
    public function dataLabelsFormatter(): string
    {
        return <<<'JS'
            if (val > 60000) {
                let hours = val / 60000;
                return hours.toFixed(2) + 'h';
            } else {
                let minutes = val / 1000;
                return minutes.toFixed(0) + 'm';
            }
        JS;
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

    #[Js]
    public function xAxisFormatter(): string
    {
        return $this->toolTipFormatter();
    }

    public function updatedTimeFrame(): void
    {
        $this->xaxis = null;
        $this->series = null;

        parent::updatedTimeFrame();
    }

    public function updatedStart(): void
    {
        $this->calculateChart();
        $this->updateData();
    }

    public function updatedEnd(): void
    {
        $this->calculateChart();
        $this->updateData();
    }

    public function calculateChart(): void
    {
        $this->xaxis = null;
        $timeFrame = TimeFrameEnum::fromName($this->timeFrame);

        $baseQuery = WorkTime::query()
            ->where('user_id', $this->userId)
            ->where('is_locked', true)
            ->when($timeFrame === TimeFrameEnum::Custom && $this->start, function ($query) {
                $query->where('started_at', '>=', Carbon::parse($this->start));
            })
            ->when($timeFrame === TimeFrameEnum::Custom && $this->end, function ($query) {
                $query->where('started_at', '<=', Carbon::parse($this->end)->endOfDay());
            });

        if ($timeFrame !== TimeFrameEnum::Custom) {
            $parameters = $timeFrame->dateQueryParameters('started_at');

            if ($parameters && count($parameters) > 0) {
                if ($parameters['operator'] === 'between') {
                    $baseQuery->whereBetween($parameters['column'], $parameters['value']);
                } else {
                    $baseQuery->where(...array_values($parameters));
                }
            }
        }

        $workDays = $baseQuery
            ->clone()
            ->where('is_daily_work_time', true)
            ->where('is_pause', false)
            ->orderBy('started_at', 'desc')
            ->get();

        $data = [
            'work_time' => [
                'name' => __('Work Time'),
                'group' => 'worktime',
                'color' => 'emerald-600',
                'data' => [],
            ],
            'pause_time' => [
                'name' => __('Paused Time'),
                'group' => 'worktime',
                'color' => 'amber-400',
                'data' => [],
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

        $activeWorkTimeTypeIds = [];
        foreach ($workDays as $day) {
            $pause = $baseQuery->clone()
                ->where('is_daily_work_time', true)
                ->where('is_pause', true)
                ->where('parent_id', $day->id)
                ->sum('total_time_ms');

            $workTime = WorkTime::query()
                ->whereKey($day->id)
                ->first();

            $data['work_time']['data'][] = ceil(($workTime->total_time_ms - $pause) / 60);
            $data['pause_time']['data'][] = ceil($pause / 60);

            $activeWorkTimeTypeIds = array_merge(
                $activeWorkTimeTypeIds,
                $baseQuery->clone()
                    ->where('is_daily_work_time', false)
                    ->where('parent_id', $day->id)
                    ->distinct()
                    ->pluck('work_time_type_id')
                    ->toArray()
            );

            $this->xaxis['categories'][] = $day->started_at->format('Y-m-d')
                . (auth()->id() === $day->user_id ? '' : '->' . $day->user->name);

        }

        $activeWorkTimeTypes = WorkTimeType::query()
            ->whereIntegerInRaw('id', $activeWorkTimeTypeIds)
            ->get();

        foreach ($workDays as $day) {
            foreach ($activeWorkTimeTypes as $activeWorkTimeType) {
                $current = data_get($data, 'task_time_' . $activeWorkTimeType->id . '.data', []);
                $taskTime = $baseQuery->clone()
                    ->where('is_daily_work_time', false)
                    ->where('work_time_type_id', $activeWorkTimeType->id)
                    ->where('parent_id', $day->id)
                    ->sum('total_time_ms');
                $current[$day->id] = ceil($taskTime / 60);

                $data['task_time_' . $activeWorkTimeType->id] = [
                    'name' => $activeWorkTimeType->name ?? __('Unknown'),
                    'group' => 'tasktime',
                    'color' => $colors[$activeWorkTimeType->id ?? 0],
                    'data' => $current,
                ];
            }

            // add unknown task time
            $current = data_get($data, 'task_time_0.data', []);
            $taskTime = $baseQuery->clone()
                ->where('is_daily_work_time', false)
                ->whereNull('work_time_type_id')
                ->where('parent_id', $day->id)
                ->sum('total_time_ms');

            $current[$day->id] = ceil($taskTime / 60);
            $data['task_time_0'] = [
                'name' => __('Unknown'),
                'group' => 'tasktime',
                'color' => $colors[0],
                'data' => $current,
            ];
        }

        $data = array_map(function ($item) {
            $item['data'] = array_values($item['data']);

            return $item;
        }, $data);

        $this->series = array_values($data);
    }
}
