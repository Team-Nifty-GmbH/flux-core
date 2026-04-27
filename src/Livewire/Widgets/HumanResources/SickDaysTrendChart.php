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

class SickDaysTrendChart extends LineChart
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
            ->whereBetween('date', [$startDate, $endDate])
            ->where('is_work_day', true);

        $sickData = Line::make($employeeDayQuery)
            ->setDateColumn('date')
            ->setRange($this->timeFrame)
            ->setEndingDate($endDate)
            ->setStartingDate($startDate)
            ->sum('sick_days_used');

        $sickDays = array_map(
            fn ($days) => bcround($days, 1),
            $sickData->getData()
        );

        $this->xaxis = [
            'categories' => $sickData->getLabels(),
        ];

        $this->series = [
            [
                'name' => __('Sick Days'),
                'data' => $sickDays,
                'color' => ChartColorEnum::Red,
            ],
        ];
    }

    #[Js]
    public function toolTipFormatter(): string
    {
        return <<<'JS'
            return val.toFixed(1) + ' days';
        JS;
    }

    #[Js]
    public function yAxisFormatter(): string
    {
        return $this->toolTipFormatter();
    }
}
