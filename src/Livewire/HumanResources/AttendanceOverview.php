<?php

namespace FluxErp\Livewire\HumanResources;

use FluxErp\Models\AbsenceType;
use FluxErp\Models\Employee;
use FluxErp\Models\EmployeeDepartment;
use FluxErp\Models\Holiday;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Url;
use Livewire\Component;

class AttendanceOverview extends Component
{
    public array $calendarDays = [];

    public array $employeesByDepartment = [];

    public array $holidays = [];

    #[Url]
    public int $month;

    public string $monthName = '';

    #[Url]
    public int $year;

    public function mount(): void
    {
        $this->year ??= now()->year;
        $this->month ??= now()->month;

        $this->loadData();
    }

    public function render(): View
    {
        return view(
            'flux::livewire.human-resources.attendance-overview',
            [
                'departments' => resolve_static(EmployeeDepartment::class, 'query')
                    ->whereIn('id', array_keys($this->employeesByDepartment))
                    ->orderBy('name')
                    ->get(['id', 'name'])
                    ->keyBy('id')
                    ->toArray(),
                'absenceTypes' => $this->getAbsenceTypes(),
            ]
        );
    }

    public function loadData(): void
    {
        $this->monthName = Carbon::create($this->year, $this->month)
            ->locale(app()->getLocale())
            ->monthName;

        $this->prepareCalendarDays();
        $this->loadEmployeesByDepartment();
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

    protected function getAbsenceTypes(): array
    {
        return resolve_static(AbsenceType::class, 'query')
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'code',
                'color',
                'percentage_deduction',
                'affects_overtime',
                'affects_sick_leave',
                'affects_vacation',
            ])
            ->map(fn (AbsenceType $type) => array_merge(
                $type->toArray(),
                [
                    'icon' => $type->code,
                ]
            ))
            ->prepend([
                'id' => 'present',
                'name' => __('Present'),
                'color' => 'green',
            ])
            ->prepend([
                'id' => 'working',
                'name' => __('Currently Working'),
                'color' => 'green',
                'icon' => '▶',
            ])
            ->prepend([
                'id' => 'absent',
                'name' => __('Unexcused absence'),
                'color' => 'red',
                'icon' => '!',
            ])
            ->prepend([
                'id' => 'holiday',
                'name' => __('Holiday'),
                'color' => '#fee685',
                'icon' => '🎉',
            ])
            ->keyBy('id')
            ->toArray();
    }

    protected function loadEmployeesByDepartment(): void
    {
        $endOfMonth = Carbon::create($this->year, $this->month)->endOfMonth();

        $this->employeesByDepartment = resolve_static(Employee::class, 'query')
            ->select([
                'id',
                'employee_department_id',
                'name',
            ])
            ->employed($endOfMonth)
            ->orderBy('name')
            ->get()
            ->groupBy('employee_department_id')
            ->map(fn ($employees) => $employees->map(fn (Employee $e) => [
                'id' => $e->getKey(),
                'name' => $e->name,
            ])->toArray())
            ->toArray();
    }

    protected function loadHolidays(): void
    {
        $this->holidays = resolve_static(Holiday::class, 'query')
            ->where(fn (Builder $query) => $query
                ->whereValueBetween($this->year, ['effective_from', 'effective_until'])
                ->orWhere(fn (Builder $query) => $query
                    ->whereNull('effective_from')
                    ->where('effective_until', '>=', $this->year)
                )
                ->orWhere(fn (Builder $query) => $query
                    ->whereNull('effective_until')
                    ->where('effective_from', '<=', $this->year)
                )
                ->orWhere(fn (Builder $query) => $query
                    ->whereNull('effective_from')
                    ->whereNull('effective_until')
                )
            )
            ->where(fn (Builder $query) => $query
                ->where('month', $this->month)
                ->whereBetween('day', [1, Carbon::create($this->year, $this->month)->daysInMonth])
                ->orWhereBetween(
                    'date',
                    [
                        Carbon::create($this->year, $this->month)->startOfMonth(),
                        Carbon::create($this->year, $this->month)->endOfMonth(),
                    ]
                )
            )
            ->with([
                'locations:id',
            ])
            ->selectRaw('id, name, COALESCE(date, (CONCAT(?, \'-\', month, \'-\', day))) AS date', [$this->year])
            ->get()
            ->map(function (Holiday $holiday) {
                $holidayArray = $holiday->toArray();
                $holidayArray['locations'] = $holiday->locations->pluck('id')->toArray();

                return $holidayArray;
            })
            ->toArray();
    }

    protected function prepareCalendarDays(): void
    {
        $this->calendarDays = [];

        for ($day = 1; $day <= Carbon::create($this->year, $this->month)->daysInMonth; $day++) {
            $date = Carbon::create($this->year, $this->month, $day);

            $this->calendarDays[$date->format('Y-m-d')] = [
                'day' => $day,
                'date' => $date->format('Y-m-d'),
                'weekDay' => substr(__($date->format('D')), 0, 2),
                'isWeekend' => $date->isWeekend(),

                'isToday' => $date->isToday(),
                'isFuture' => $date->isFuture(),
            ];
        }
    }
}
