<?php

namespace FluxErp\Livewire\Widgets\HumanResources;

use FluxErp\Enums\ChartColorEnum;
use FluxErp\Livewire\HumanResources\Dashboard;
use FluxErp\Livewire\Support\Widgets\Charts\CircleChart;
use FluxErp\Models\Employee;
use FluxErp\Models\Pivots\EmployeeWorkTimeModel;
use FluxErp\Models\WorkTimeModel;
use FluxErp\Traits\Livewire\Widget\Widgetable;

class WorkTimeModelDistributionChart extends CircleChart
{
    use Widgetable;

    public ?array $chart = [
        'type' => 'donut',
    ];

    public static function getCategory(): ?string
    {
        return 'Human Resources';
    }

    public static function dashboardComponent(): array|string
    {
        return Dashboard::class;
    }

    public static function getDefaultOrderColumn(): int
    {
        return 2;
    }

    public static function getDefaultOrderRow(): int
    {
        return 2;
    }

    public function calculateChart(): void
    {
        $now = now();

        $employedIds = resolve_static(Employee::class, 'query')
            ->employed($now)
            ->pluck('id');

        if ($employedIds->isEmpty()) {
            $this->series = [];
            $this->labels = [];

            return;
        }

        $workTimeModelTable = app(WorkTimeModel::class)->getTable();
        $pivotTable = app(EmployeeWorkTimeModel::class)->getTable();

        $distribution = resolve_static(EmployeeWorkTimeModel::class, 'query')
            ->join($workTimeModelTable, $pivotTable . '.work_time_model_id', '=', $workTimeModelTable . '.id')
            ->whereIntegerInRaw($pivotTable . '.employee_id', $employedIds)
            ->where($pivotTable . '.valid_from', '<=', $now)
            ->where(fn ($query) => $query->whereNull($pivotTable . '.valid_until')
                ->orWhere($pivotTable . '.valid_until', '>=', $now))
            ->selectRaw(
                $workTimeModelTable . '.name, '
                . $workTimeModelTable . '.id, '
                . 'count(' . $pivotTable . '.employee_id) as employee_count'
            )
            ->groupBy($workTimeModelTable . '.id', $workTimeModelTable . '.name')
            ->get();

        $labels = [];
        $series = [];
        $colors = [];

        foreach ($distribution as $index => $row) {
            $labels[] = $row->name;
            $series[] = (int) $row->employee_count;
            $colors[] = resolve_static(
                ChartColorEnum::class,
                'forIndex',
                ['index' => $index]
            )
                ->value;
        }

        $this->labels = $labels;
        $this->series = $series;
        $this->colors = $colors;
    }

    public function getPlotOptions(): array
    {
        return [
            'pie' => [
                'donut' => [
                    'labels' => [
                        'show' => true,
                        'total' => [
                            'show' => true,
                            'label' => __('Employees'),
                        ],
                    ],
                ],
            ],
        ];
    }
}
