<?php

namespace FluxErp\Livewire\Widgets\HumanResources;

use FluxErp\Enums\ChartColorEnum;
use FluxErp\Livewire\HumanResources\Dashboard;
use FluxErp\Livewire\Support\Widgets\Charts\LineChart;
use FluxErp\Models\EmployeeDay;
use FluxErp\Support\Metrics\Charts\Line;
use FluxErp\Traits\Livewire\Widget\HasTemporalXAxisFormatter;
use FluxErp\Traits\Livewire\Widget\IsTimeFrameAwareWidget;
use Livewire\Attributes\Js;

class OvertimeTrendChart extends LineChart
{
    use HasTemporalXAxisFormatter, IsTimeFrameAwareWidget;

    public ?array $markers = [
        'size' => 4,
    ];

    public ?array $stroke = [
        'show' => true,
        'width' => 3,
        'curve' => 'smooth',
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
        return 4;
    }

    public static function getDefaultOrderRow(): int
    {
        return 2;
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

        $overtimeData = Line::make($employeeDayQuery)
            ->setDateColumn('date')
            ->setRange($this->timeFrame)
            ->setEndingDate($endDate)
            ->setStartingDate($startDate)
            ->sum('plus_minus_overtime_hours');

        $overtimeHours = array_map(
            fn ($hours) => bcround($hours, 2),
            $overtimeData->getData()
        );

        $this->xaxis = [
            'categories' => $overtimeData->getLabels(),
        ];

        $this->series = [
            [
                'name' => __('Overtime Hours'),
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
