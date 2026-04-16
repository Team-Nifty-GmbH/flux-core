<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Enums\AbsenceRequestStateEnum;
use FluxErp\Enums\DayPartEnum;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\HumanResources\Dashboard as HumanResourcesDashboard;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\AbsenceType;
use FluxErp\Models\Employee;
use FluxErp\Models\Holiday;
use FluxErp\Traits\Livewire\Widget\Widgetable;
use Illuminate\Contracts\View\View;
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
        return [Dashboard::class, HumanResourcesDashboard::class];
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
        $this->month = max(1, min(12, $this->month));
        $this->year = max(1970, min(2100, $this->year));

        $this->monthName = Carbon::create($this->year, $this->month)
            ->locale(app()->getLocale())
            ->monthName;

        $this->prepareCalendarDays();
        $this->loadAbsenceTypes();
        $this->loadDepartments();
    }

    protected function holidayAbsenceType(): array
    {
        return [
            'id' => 'holiday',
            'name' => __('Holiday'),
            'code' => '🎉',
            'color' => '#fee685',
        ];
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
                'weekDay' => $date->locale(app()->getLocale())->shortDayName,
                'isWeekend' => $date->isWeekend(),
                'isToday' => $date->isToday(),
            ];
        }
    }

    protected function loadAbsenceTypes(): void
    {
        $holiday = $this->holidayAbsenceType();

        $this->absenceTypes = resolve_static(AbsenceType::class, 'query')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'color'])
            ->map(fn (AbsenceType $type) => [
                'id' => $type->id,
                'name' => $type->name,
                'code' => $type->code,
                'color' => $type->color,
            ])
            ->prepend($holiday)
            ->keyBy('id')
            ->toArray();
    }

    protected function loadDepartments(): void
    {
        $startOfMonth = Carbon::create($this->year, $this->month)->startOfMonth();
        $endOfMonth = Carbon::create($this->year, $this->month)->endOfMonth();

        $employees = resolve_static(Employee::class, 'query')
            ->select(['id', 'employee_department_id', 'name'])
            ->with('employeeDepartment:id,name')
            ->employed($endOfMonth)
            ->orderBy('name')
            ->get();

        $employeeIds = $employees->pluck('id')->toArray();

        $absences = resolve_static(AbsenceRequest::class, 'query')
            ->whereIntegerInRaw('employee_id', $employeeIds)
            ->whereIn('state', [AbsenceRequestStateEnum::Approved, AbsenceRequestStateEnum::Pending])
            ->where('start_date', '<=', $endOfMonth)
            ->where('end_date', '>=', $startOfMonth)
            ->with('absenceType:id,name,code,color')
            ->get([
                'id', 'employee_id', 'absence_type_id',
                'start_date', 'end_date', 'day_part', 'state',
            ])
            ->groupBy('employee_id');

        $holidays = resolve_static(Holiday::class, 'query')
            ->where('is_active', true)
            ->where(fn ($query) => $query
                ->whereValueBetween($startOfMonth->year, ['effective_from', 'effective_until'])
                ->orWhere(fn ($query) => $query
                    ->whereNull('effective_from')
                    ->where('effective_until', '>=', $startOfMonth->year)
                )
                ->orWhere(fn ($query) => $query
                    ->whereNull('effective_until')
                    ->where('effective_from', '<=', $startOfMonth->year)
                )
                ->orWhere(fn ($query) => $query
                    ->whereNull('effective_from')
                    ->whereNull('effective_until')
                )
            )
            ->where(fn ($query) => $query
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->orWhere(fn ($query) => $query
                    ->whereNull('date')
                    ->where('month', $startOfMonth->month)
                )
            )
            ->get(['id', 'name', 'date', 'month', 'day', 'is_half_day'])
            ->keyBy(fn (Holiday $holiday) => $holiday->date
                ? $holiday->date->format('Y-m-d')
                : Carbon::create($startOfMonth->year, $holiday->month, $holiday->day)->format('Y-m-d')
            );

        $holidayType = $this->holidayAbsenceType();

        $this->departments = $employees
            ->groupBy('employee_department_id')
            ->map(function ($deptEmployees) use ($absences, $holidays, $holidayType, $startOfMonth, $endOfMonth) {
                $department = $deptEmployees->first()->employeeDepartment;

                return [
                    'name' => $department?->getLabel() ?? __('Unknown Department'),
                    'employees' => $deptEmployees->map(
                        function (Employee $employee) use ($absences, $holidays, $holidayType, $startOfMonth, $endOfMonth) {
                            $employeeAbsences = $absences->get($employee->getKey()) ?? [];
                            $days = [];

                            foreach ($holidays as $dateKey => $holiday) {
                                $days[$dateKey] = [
                                    'type' => $holidayType['id'],
                                    'color' => $holidayType['color'],
                                    'name' => $holiday->name,
                                    'is_half_day' => $holiday->is_half_day,
                                    'is_holiday' => true,
                                    'holiday_name' => $holiday->name,
                                ];
                            }

                            foreach ($employeeAbsences as $absence) {
                                $start = $absence->start_date->copy()->max($startOfMonth);
                                $end = $absence->end_date->copy()->min($endOfMonth);

                                while ($start->lte($end)) {
                                    $dateKey = $start->format('Y-m-d');
                                    $isPending = $absence->state === AbsenceRequestStateEnum::Pending;

                                    if ($isPending && isset($days[$dateKey]) && ! ($days[$dateKey]['pending'] ?? false)) {
                                        $start->addDay();

                                        continue;
                                    }

                                    $existingDay = $days[$dateKey] ?? null;
                                    $isAbsenceHalfDay = $absence->day_part
                                        && $absence->day_part->value !== DayPartEnum::FullDay;
                                    $holidayName = $existingDay['holiday_name'] ?? null;
                                    $isOnHoliday = ($existingDay['type'] ?? null) === $holidayType['id'];

                                    $days[$dateKey] = [
                                        'type' => $isOnHoliday ? 'split' : 'absence',
                                        'color' => $absence->absenceType?->color,
                                        'name' => $absence->absenceType?->name
                                            . ($isPending ? ' (' . __('pending') . ')' : ''),
                                        'is_half_day' => $isAbsenceHalfDay && ! $isOnHoliday,
                                        'pending' => $isPending,
                                        'is_holiday' => (bool) $holidayName,
                                        'holiday_name' => $holidayName,
                                        'holiday_color' => $isOnHoliday ? $holidayType['color'] : null,
                                    ];
                                    $start->addDay();
                                }
                            }

                            return [
                                'id' => $employee->getKey(),
                                'name' => $employee->name,
                                'days' => $days,
                            ];
                        }
                    )->values()->toArray(),
                ];
            })
            ->sortBy('name')
            ->values()
            ->toArray();
    }
}
