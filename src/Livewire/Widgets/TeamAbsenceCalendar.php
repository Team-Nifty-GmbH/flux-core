<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Enums\AbsenceRequestStateEnum;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\AbsenceType;
use FluxErp\Models\Employee;
use FluxErp\Models\EmployeeDay;
use FluxErp\Models\EmployeeDepartment;
use FluxErp\Traits\Livewire\Widget\Widgetable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Livewire\Component;

class TeamAbsenceCalendar extends Component
{
    use Widgetable;

    public int $month;

    public string $monthName = '';

    public int $year;

    public array $calendarDays = [];

    public array $departments = [];

    public array $absenceTypes = [];

    public static function dashboardComponent(): array|string
    {
        return Dashboard::class;
    }

    public static function getCategory(): ?string
    {
        return 'Employees';
    }

    public static function getLabel(): string
    {
        return __('Team Absence Calendar');
    }

    public static function getDefaultWidth(): int
    {
        return 4;
    }

    public static function getDefaultHeight(): int
    {
        return 2;
    }

    public function mount(): void
    {
        $this->year = now()->year;
        $this->month = now()->month;

        $this->loadData();
    }

    public function render(): View
    {
        return view('flux::livewire.widgets.team-absence-calendar');
    }

    public function nextMonth(): void
    {
        $this->month++;
        if ($this->month > 12) {
            $this->month = 1;
            $this->year++;
        }

        $this->loadData();
    }

    public function previousMonth(): void
    {
        $this->month--;
        if ($this->month < 1) {
            $this->month = 12;
            $this->year--;
        }

        $this->loadData();
    }

    protected function loadData(): void
    {
        $this->monthName = Carbon::create($this->year, $this->month)
            ->locale(app()->getLocale())
            ->monthName;

        $this->prepareCalendarDays();
        $this->loadAbsenceTypes();
        $this->loadDepartments();
    }

    protected function prepareCalendarDays(): void
    {
        $this->calendarDays = [];
        $daysInMonth = Carbon::create($this->year, $this->month)->daysInMonth;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($this->year, $this->month, $day);

            $this->calendarDays[$date->format('Y-m-d')] = [
                'day' => $day,
                'date' => $date->format('Y-m-d'),
                'weekDay' => substr(__($date->format('D')), 0, 2),
                'isWeekend' => $date->isWeekend(),
                'isToday' => $date->isToday(),
            ];
        }
    }

    protected function loadAbsenceTypes(): void
    {
        $this->absenceTypes = resolve_static(AbsenceType::class, 'query')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'color'])
            ->prepend(new AbsenceType([
                'id' => 'holiday',
                'name' => __('Holiday'),
                'code' => '🎉',
                'color' => '#fee685',
            ]))
            ->map(fn (AbsenceType $type) => [
                'id' => $type->id,
                'name' => $type->name,
                'code' => $type->code,
                'color' => $type->color,
            ])
            ->keyBy('id')
            ->toArray();
    }

    protected function loadDepartments(): void
    {
        $startOfMonth = Carbon::create($this->year, $this->month)->startOfMonth();
        $endOfMonth = Carbon::create($this->year, $this->month)->endOfMonth();

        $employees = resolve_static(Employee::class, 'query')
            ->select(['id', 'employee_department_id', 'name'])
            ->employed($endOfMonth)
            ->orderBy('name')
            ->get();

        $employeeIds = $employees->pluck('id');

        $absences = resolve_static(AbsenceRequest::class, 'query')
            ->whereIn('employee_id', $employeeIds)
            ->where('state', AbsenceRequestStateEnum::Approved)
            ->where(fn (Builder $query) => $query
                ->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                ->orWhereBetween('end_date', [$startOfMonth, $endOfMonth])
                ->orWhere(fn (Builder $query) => $query
                    ->where('start_date', '<=', $startOfMonth)
                    ->where('end_date', '>=', $endOfMonth)
                )
            )
            ->with('absenceType:id,name,code,color')
            ->get(['id', 'employee_id', 'absence_type_id', 'start_date', 'end_date', 'day_part'])
            ->groupBy('employee_id');

        $employeeHolidays = resolve_static(EmployeeDay::class, 'query')
            ->whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->where('is_holiday', true)
            ->get(['employee_id', 'date'])
            ->groupBy('employee_id')
            ->map(fn ($days) => $days->pluck('date')->map(fn ($date) => Carbon::parse($date)->format('Y-m-d'))->toArray());

        $departmentIds = $employees->pluck('employee_department_id')->unique()->filter();
        $departmentNames = resolve_static(EmployeeDepartment::class, 'query')
            ->whereIn('id', $departmentIds)
            ->pluck('name', 'id');

        $this->departments = $employees
            ->groupBy('employee_department_id')
            ->map(function ($deptEmployees, $departmentId) use ($absences, $employeeHolidays, $departmentNames) {
                return [
                    'name' => $departmentNames[$departmentId] ?? __('Unknown Department'),
                    'employees' => $deptEmployees->map(function (Employee $employee) use ($absences, $employeeHolidays) {
                        $employeeAbsences = $absences->get($employee->getKey(), collect());
                        $holidays = $employeeHolidays->get($employee->getKey(), []);

                        $days = [];

                        foreach ($holidays as $dateKey) {
                            $days[$dateKey] = [
                                'type' => 'holiday',
                                'color' => '#fee685',
                                'name' => __('Holiday'),
                                'is_half_day' => false,
                            ];
                        }

                        foreach ($employeeAbsences as $absence) {
                            $start = $absence->start_date->copy()->max(
                                Carbon::create($this->year, $this->month)->startOfMonth()
                            );
                            $end = $absence->end_date->copy()->min(
                                Carbon::create($this->year, $this->month)->endOfMonth()
                            );

                            while ($start->lte($end)) {
                                $dateKey = $start->format('Y-m-d');
                                $days[$dateKey] = [
                                    'type' => 'absence',
                                    'color' => $absence->absenceType?->color,
                                    'name' => $absence->absenceType?->name,
                                    'is_half_day' => $absence->day_part
                                        && $absence->day_part->value !== 'full_day',
                                ];
                                $start->addDay();
                            }
                        }

                        return [
                            'id' => $employee->getKey(),
                            'name' => $employee->name,
                            'days' => $days,
                        ];
                    })->values()->toArray(),
                ];
            })
            ->sortBy('name')
            ->values()
            ->toArray();
    }
}
