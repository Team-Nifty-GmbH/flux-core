<?php

namespace FluxErp\Livewire\Widgets\Employee;

use Carbon\Carbon;
use FluxErp\Enums\AbsenceRequestStateEnum;
use FluxErp\Livewire\Employee\Dashboard;
use FluxErp\Livewire\Support\Widgets\Charts\CircleChart;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\Employee;
use FluxErp\Models\EmployeeDay;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;

class AttendanceOverview extends CircleChart
{
    use IsTimeFrameAwareWidget;

    public ?array $chart = [
        'type' => 'donut',
    ];

    #[Locked]
    public ?int $employeeId = null;

    public bool $showTotals = true;

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

    public static function getLabel(): string
    {
        return __('Attendance Overview');
    }

    #[Renderless]
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

        $startDate = $this->getStart();
        $endDate = $this->getEnd();

        $totalWorkDays = resolve_static(EmployeeDay::class, 'query')
            ->where('employee_id', $employee->getKey())
            ->whereBetween('date', [$startDate, $endDate])
            ->where('is_work_day', true)
            ->count();

        if (bccomp($totalWorkDays, 0) === 0) {
            $this->series = [];
            $this->labels = [];

            return;
        }

        $absenceRequests = resolve_static(AbsenceRequest::class, 'query')
            ->where('employee_id', $employee->getKey())
            ->whereHas('employeeDays', function (Builder $query) use ($startDate, $endDate): void {
                $query->whereBetween('date', [$startDate, $endDate]);
            })
            ->where('status', AbsenceRequestStateEnum::Approved)
            ->with(['absenceType', 'employeeDays'])
            ->get();

        $absenceByType = [];
        foreach ($absenceRequests as $request) {
            $typeId = $request->absence_type_id;
            $startDate = max($request->start_date, Carbon::create($this->getStart()));
            $endDate = min($request->end_date, $this->getEnd());
            $daysAffected = 0;

            while ($startDate <= $endDate) {
                $daysAffected = bcadd($daysAffected, $request->calculateWorkDaysAffected($startDate));

                $startDate->addDay();
            }

            if (! data_get($absenceByType, $typeId)) {
                $absenceByType[$typeId] = [
                    'name' => $request->absenceType->name,
                    'color' => $request->absenceType->color ?? '#6b7280',
                    'days' => 0,
                ];
            }

            $absenceByType[$typeId]['days'] = bcadd(data_get($absenceByType, $typeId . '.days'), $daysAffected);
        }

        $unexcusedDays = resolve_static(EmployeeDay::class, 'query')
            ->where('employee_id', $employee->getKey())
            ->whereBetween('date', [$startDate, $endDate])
            ->where('is_work_day', true)
            ->whereDoesntHave('workTimes')
            ->whereDoesntHave('absenceRequests', function (Builder $query): void {
                $query->where('status', AbsenceRequestStateEnum::Approved);
            })
            ->count();

        $attendanceDays = resolve_static(EmployeeDay::class, 'query')
            ->where('employee_id', $employee->getKey())
            ->whereBetween('date', [$startDate, $endDate])
            ->where('is_work_day', true)
            ->where('actual_hours', '>', 0)
            ->count();

        $attendanceDays = (string) $attendanceDays;
        $unexcusedDays = (string) $unexcusedDays;

        $labels = [];
        $series = [];
        $colors = [];

        $labels[] = __('Remaining Vacation Days');
        $series[] = Number::format(bcround($employee->getCurrentVacationDaysBalance(), 2), 2);
        $colors[] = '#3b82f6';

        if (bccomp($attendanceDays, 0) > 0) {
            $labels[] = __('Attendance');
            $series[] = Number::format(bcround($attendanceDays, 2), 2);
            $colors[] = '#10b981';
        }

        foreach ($absenceByType as $typeData) {
            if (bccomp($typeData['days'], '0', 2) > 0) {
                $labels[] = $typeData['name'];
                $roundedDays = bcround($typeData['days'], 2);
                $series[] = Number::format($roundedDays, 2);
                $colors[] = $typeData['color'];
            }
        }

        if (bccomp($unexcusedDays, '0', 2) > 0) {
            $labels[] = __('Unexcused Absence');
            $roundedDays = bcround($unexcusedDays, 2);
            $series[] = Number::format($roundedDays, 2);
            $colors[] = '#dc2626';
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
                            'label' => __('Days'),
                        ],
                    ],
                ],
            ],
        ];
    }

    public function showTitle(): bool
    {
        return true;
    }
}
