<?php

namespace FluxErp\Livewire\Widgets\Employee;

use FluxErp\Livewire\Employee\Dashboard;
use FluxErp\Livewire\Support\Widgets\Charts\LineChart;
use FluxErp\Models\Employee;
use FluxErp\Models\EmployeeDay;
use FluxErp\Support\Metrics\Charts\Line;
use FluxErp\Traits\Livewire\HasTemporalXAxisFormatter;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use Livewire\Attributes\Js;
use Livewire\Attributes\Locked;

class WorkTimeOverview extends LineChart
{
    use HasTemporalXAxisFormatter, IsTimeFrameAwareWidget;

    public ?array $chart = [
        'type' => 'line',
    ];

    #[Locked]
    public ?int $employeeId = null;

    public ?array $markers = [
        'size' => 4,
    ];

    public ?array $stroke = [
        'show' => true,
        'width' => 3,
        'curve' => 'smooth',
    ];

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
        return 1;
    }

    public function calculateByTimeFrame(): void
    {
        $this->calculateChart();
        $this->updateData();
    }

    public function calculateChart(): void
    {
        $employee = resolve_static(Employee::class, 'query')
            ->whereKey($this->employeeId)
            ->first();

        if (! $employee) {
            $this->series = [];
            $this->xaxis = null;

            return;
        }

        $startDate = $this->getStart();
        $endDate = $this->getEnd();

        $employeeDayQuery = resolve_static(EmployeeDay::class, 'query')
            ->where('employee_id', $employee->getKey())
            ->whereBetween('date', [$startDate, $endDate]);

        $actualWorkData = Line::make($employeeDayQuery->clone())
            ->setDateColumn('date')
            ->setRange($this->timeFrame)
            ->setEndingDate($endDate)
            ->setStartingDate($startDate)
            ->sum('actual_hours');

        $targetData = Line::make($employeeDayQuery->clone())
            ->setDateColumn('date')
            ->setRange($this->timeFrame)
            ->setEndingDate($endDate)
            ->setStartingDate($startDate)
            ->sum('target_hours');

        $vacationData = Line::make($employeeDayQuery->clone())
            ->setDateColumn('date')
            ->setRange($this->timeFrame)
            ->setEndingDate($endDate)
            ->setStartingDate($startDate)
            ->sum('vacation_hours_used');

        $sickData = Line::make($employeeDayQuery->clone())
            ->setDateColumn('date')
            ->setRange($this->timeFrame)
            ->setEndingDate($endDate)
            ->setStartingDate($startDate)
            ->sum('sick_hours_used');

        $overtimeData = Line::make($employeeDayQuery->clone())
            ->setDateColumn('date')
            ->setRange($this->timeFrame)
            ->setEndingDate($endDate)
            ->setStartingDate($startDate)
            ->sum('plus_minus_overtime_hours');

        $actualHours = array_map(
            function ($hours) {
                return bcround($hours, 2);
            },
            $actualWorkData->getData()
        );

        $effectiveTargetHours = [];
        $vacationRaw = $vacationData->getData();
        $sickRaw = $sickData->getData();

        foreach ($targetData->getData() as $index => $target) {
            $vacation = bcabs($vacationRaw[$index] ?? 0);
            $sick = bcabs($sickRaw[$index] ?? 0);
            $effective = bcsub($target, bcadd($vacation, $sick, 2), 2);
            $effectiveTargetHours[] = $effective;
        }

        $overtimeHours = array_map(
            function ($hours) {
                return bcround($hours, 2);
            },
            $overtimeData->getData()
        );

        $this->xaxis = [
            'categories' => $actualWorkData->getLabels(),
        ];

        $this->series = [
            [
                'name' => __('Actual Work Time'),
                'data' => $actualHours,
                'color' => '#3b82f6',
                'zIndex' => 2,
            ],
            [
                'name' => __('Target'),
                'data' => $effectiveTargetHours,
                'color' => '#10b981',
                'zIndex' => 1,
            ],
            [
                'name' => __('Overtime'),
                'data' => $overtimeHours,
                'color' => '#ef4444',
                'zIndex' => 0,
            ],
        ];
    }

    #[Js]
    public function dataLabelsFormatter(): string
    {
        return $this->toolTipFormatter();
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
