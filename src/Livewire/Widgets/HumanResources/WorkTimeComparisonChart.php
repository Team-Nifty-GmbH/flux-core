<?php

namespace FluxErp\Livewire\Widgets\HumanResources;

use FluxErp\Enums\ChartColorEnum;
use FluxErp\Livewire\HumanResources\Dashboard;
use FluxErp\Livewire\Support\Widgets\Charts\BarChart;
use FluxErp\Models\EmployeeDay;
use FluxErp\Support\Metrics\Charts\Line;
use FluxErp\Traits\Livewire\Widget\HasTemporalXAxisFormatter;
use FluxErp\Traits\Livewire\Widget\IsTimeFrameAwareWidget;
use Livewire\Attributes\Js;

class WorkTimeComparisonChart extends BarChart
{
    use HasTemporalXAxisFormatter, IsTimeFrameAwareWidget;

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
        return 0;
    }

    public static function getDefaultOrderRow(): int
    {
        return 1;
    }

    public function calculateByTimeFrame(): void
    {
        $this->calculateChart();
        $this->updateData();
    }

    public function calculateChart(): void
    {
        $startDate = $this->getStart();
        $endDate = $this->getEnd();

        $employeeDayQuery = resolve_static(EmployeeDay::class, 'query')
            ->whereBetween('date', [$startDate, $endDate]);

        $targetData = Line::make($employeeDayQuery->clone())
            ->setDateColumn('date')
            ->setRange($this->timeFrame)
            ->setEndingDate($endDate)
            ->setStartingDate($startDate)
            ->sum('target_hours');

        $actualData = Line::make($employeeDayQuery->clone())
            ->setDateColumn('date')
            ->setRange($this->timeFrame)
            ->setEndingDate($endDate)
            ->setStartingDate($startDate)
            ->sum('actual_hours');

        $overtimeData = Line::make($employeeDayQuery->clone())
            ->setDateColumn('date')
            ->setRange($this->timeFrame)
            ->setEndingDate($endDate)
            ->setStartingDate($startDate)
            ->sum('plus_minus_overtime_hours');

        $targetHours = array_map(
            fn ($hours) => bcround($hours, 2),
            $targetData->getData()
        );

        $actualHours = array_map(
            fn ($hours) => bcround($hours, 2),
            $actualData->getData()
        );

        $overtimeHours = array_map(
            fn ($hours) => bcround($hours, 2),
            $overtimeData->getData()
        );

        $this->xaxis = [
            'categories' => $targetData->getLabels(),
        ];

        $this->series = [
            [
                'name' => __('Target Hours'),
                'data' => $targetHours,
                'color' => ChartColorEnum::Emerald,
            ],
            [
                'name' => __('Actual Hours'),
                'data' => $actualHours,
                'color' => ChartColorEnum::Blue,
            ],
            [
                'name' => __('Overtime'),
                'data' => $overtimeHours,
                'color' => ChartColorEnum::Red,
            ],
        ];
    }

    #[Js]
    public function toolTipFormatter(): string
    {
        return <<<'JS'
            return val.toFixed(2) + 'h';
        JS;
    }

    #[Js]
    public function yAxisFormatter(): string
    {
        return $this->toolTipFormatter();
    }
}
