<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Livewire\Charts\BarChart;
use FluxErp\Models\WorkTime;
use FluxErp\Traits\Widgetable;
use Livewire\Attributes\Js;

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

    #[Js]
    public function toolTipFormatter(): string
    {
        return <<<'JS'
            let hours = val / 60;
            return hours.toFixed(2) + 'h';
        JS;
    }

    #[Js]
    public function dataLabelsFormatter(): string
    {
        return <<<'JS'
            if (val > 60) {
                let hours = val / 60;
                return hours.toFixed(2) + 'h';
            } else {
                return val + ' min.';
            }
        JS;
    }

    #[Js]
    public function yAxisFormatter(): string
    {
        return <<<'JS'
            return new Date(val).toLocaleDateString(document.documentElement.lang);
        JS;
    }

    #[Js]
    public function xAxisFormatter(): string
    {
        return $this->toolTipFormatter();
    }

    public function calculateChart(): void
    {
        $baseQuery = WorkTime::query()
            ->where('user_id', auth()->id())
            ->where('is_locked', true);

        $timeFrame = TimeFrameEnum::fromName($this->timeFrame);
        $parameters = $timeFrame->dateQueryParameters('started_at');

        if ($parameters && count($parameters) > 0) {
            if ($parameters['operator'] === 'between') {
                $baseQuery->whereBetween($parameters['column'], $parameters['value']);
            } else {
                $baseQuery->where(...array_values($parameters));
            }
        }

        $this->xaxis['categories'] = $baseQuery
            ->clone()
            ->where('is_daily_work_time', true)
            ->where('is_pause', false)
            ->orderBy('started_at', 'desc')
            ->get()
            ->pluck('started_at')
            ->map(fn ($value) => $value->format('Y-m-d'))
            ->toArray();

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

        foreach ($this->xaxis['categories'] as $day) {
            $pause = $baseQuery->clone()
                ->where('is_daily_work_time', true)
                ->where('is_pause', true)
                ->where('started_at', 'like', $day . '%')
                ->sum('total_time');
            $workTime = $baseQuery->clone()
                ->where('is_daily_work_time', true)
                ->where('is_pause', false)
                ->where('started_at', 'like', $day . '%')
                ->sum('total_time');
            $data['work_time']['data'][] = ceil(($workTime - $pause) / 60);
            $data['pause_time']['data'][] = ceil($pause / 60);
            $taskTime = $baseQuery->clone()
                ->where('is_daily_work_time', false)
                ->where('is_pause', false)
                ->where('started_at', 'like', $day . '%')
                ->groupBy('work_time_type_id')
                ->selectRaw('ROUND(SUM(total_time), 2) as total, work_time_type_id')
                ->get();

            foreach ($taskTime as $item) {
                $data['task_time_' . $item->work_time_type_id] = [
                    'name' => $item->workTimeType?->name ?? __('Unknown'),
                    'group' => 'tasktime',
                    'color' => $colors[$item->work_time_type_id ?? 0],
                    'data' => array_merge(data_get($data, 'task_time_' . $item->work_time_type_id . '.data', []), [ceil($item->total / 60)]),
                ];
            }
        }

        $this->series = array_values($data);
    }
}
