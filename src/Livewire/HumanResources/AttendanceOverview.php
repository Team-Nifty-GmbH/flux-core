<?php

namespace FluxErp\Livewire\HumanResources;

use FluxErp\Actions\EmployeeDay\CloseEmployeeDay;
use FluxErp\Enums\AbsenceRequestStatusEnum;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\AbsenceType;
use FluxErp\Models\Employee;
use FluxErp\Models\EmployeeDay;
use FluxErp\Models\EmployeeDepartment;
use FluxErp\Models\Holiday;
use FluxErp\Models\Location;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;

class AttendanceOverview extends Component
{
    public array $attendanceData = [];

    public array $calendarDays = [];

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
                    ->whereIn('id', array_keys($this->attendanceData))
                    ->orderBy('name')
                    ->get(['id', 'name'])
                    ->keyBy('id')
                    ->toArray(),
                'absenceTypes' => resolve_static(AbsenceType::class, 'query')
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get([
                        'id',
                        'name',
                        'color',
                        'percentage_deduction',
                        'affects_overtime',
                        'affects_vacation',
                        'affects_sick',
                    ])
                    ->map(fn (AbsenceType $type) => array_merge(
                        $type->toArray(),
                        [
                            'icon' => $type->getAbbreviation(),
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
                    ->toArray(),
            ]
        );
    }

    public function loadData(): void
    {
        $this->monthName = Carbon::create($this->year, $this->month)
            ->locale(app()->getLocale())
            ->monthName;
        $this->loadHolidays();
        $this->prepareCalendarDays();
        $this->loadAttendanceData();
    }

    #[Renderless]
    public function nextMonth(): void
    {
        $this->month++;
        if ($this->month > 12) {
            $this->month = 1;
            $this->year++;
        }
        $this->loadData();
    }

    #[Renderless]
    public function previousMonth(): void
    {
        $this->month--;
        if ($this->month < 1) {
            $this->month = 12;
            $this->year--;
        }
        $this->loadData();
    }

    #[Renderless]
    public function showAbsenceRequest(AbsenceRequest $absenceRequest): void
    {
        $this->redirect($absenceRequest->getUrl(), navigate: true);
    }

    #[Renderless]
    public function showEmployee(Employee $employee): void
    {
        $this->redirect($employee->getUrl(), navigate: true);
    }

    #[Renderless]
    public function showEmployeeDay(EmployeeDay $employeeDay): void
    {
        $this->redirect($employeeDay->getUrl(), navigate: true);
    }

    public function showWorkTime(Employee $employee, ?string $date = null): void
    {
        if ($date) {
            $date = Carbon::parse($date);
            $name = __(
                'Work times for :user on :date',
                [
                    'user' => $employee->name,
                    'date' => $date->locale(app()->getLocale())->isoFormat('L'),
                ]
            );
        } else {
            $name = __(
                'Work times for :user in :month',
                [
                    'user' => $employee->name,
                    'month' => Carbon::create($this->year, $this->month)
                        ->locale(app()->getLocale())
                        ->translatedFormat('F Y'),
                ]
            );
        }

        SessionFilter::make(
            Livewire::new(resolve_static(WorkTimes::class, 'class'))->getCacheKey(),
            fn (Builder $query) => $query
                ->where('employee_id', $employee->getKey())
                ->where('is_daily_work_time', true)
                ->when(
                    $date,
                    fn (Builder $query) => $query->whereDate('started_at', $date),
                )
                ->when(
                    ! $date,
                    fn (Builder $query) => $query
                        ->whereMonth('started_at', $this->month)
                        ->whereYear('started_at', $this->year)
                ),
            $name
        )
            ->store();

        $this->redirectRoute('human-resources.work-times', navigate: true);
    }

    protected function loadAttendanceData(): void
    {
        $startOfMonth = Carbon::create($this->year, $this->month);
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $calendarDays = array_fill_keys(array_keys($this->calendarDays), []);

        $this->attendanceData = resolve_static(Employee::class, 'query')
            ->select([
                'id',
                'user_id',
                'employee_department_id',
                'location_id',
                'name',
                'employment_date',
                'termination_date',
            ])
            ->withSum([
                'employeeDays as target_hours' => fn (Builder $query) => $query
                    ->whereBetween('date', [$startOfMonth, $endOfMonth->endOfDay()])
                    ->whereDoesntHaveRelation(
                        'absenceRequests',
                        'status',
                        AbsenceRequestStatusEnum::Approved
                    ),
            ],
                'target_hours'
            )
            ->withSum([
                'employeeDays as actual_hours' => fn (Builder $query) => $query
                    ->whereBetween('date', [$startOfMonth, $endOfMonth->endOfDay()]),
            ],
                'actual_hours'
            )
            ->withCount([
                'employeeDays as target_days' => fn (Builder $query) => $query
                    ->whereBetween('date', [$startOfMonth, $endOfMonth])
                    ->where('is_work_day', true)
                    ->whereDoesntHave('absenceRequests', fn (Builder $query) => $query
                        ->where('status', AbsenceRequestStatusEnum::Approved)
                    ),
            ],
                'target_days'
            )
            ->withCount([
                'employeeDays as actual_days' => fn (Builder $query) => $query
                    ->whereBetween('date', [$startOfMonth, $endOfMonth])
                    ->where('is_work_day', true)
                    ->whereHas('workTimes'),
            ],
                'actual_days'
            )
            ->with([
                'workTimes' => fn (HasMany $query) => $query
                    ->whereBetween('started_at', [$startOfMonth, $endOfMonth->endOfDay()])
                    ->where('is_locked', false)
                    ->where('is_daily_work_time', true)
                    ->where('is_pause', false)
                    ->select([
                        'id',
                        'employee_id',
                        'started_at',
                        'ended_at',
                        'is_locked',
                        'is_daily_work_time',
                    ]),
                'employeeDays' => fn (HasMany $query) => $query
                    ->whereBetween('date', [$startOfMonth, $endOfMonth])
                    ->with([
                        'absenceRequests' => fn (BelongsToMany $query) => $query
                            ->where('status', AbsenceRequestStatusEnum::Approved)
                            ->select([
                                'id',
                                'employee_id',
                                'absence_type_id',
                                'start_date',
                                'end_date',
                                'status',
                            ]),
                    ])
                    ->select([
                        'id',
                        'employee_id',
                        'holiday_id',
                        'date',
                        'target_hours',
                        'actual_hours',
                        'vacation_days_used',
                        'sick_days_used',
                        'plus_minus_overtime_hours',
                        'is_holiday',
                        'is_work_day',
                    ]),
                'absenceRequests' => fn (HasMany $query) => $query->where(fn (Builder $query) => $query
                    ->whereValueBetween($startOfMonth->format('Y-m-d'), ['start_date', 'end_date'])
                    ->orWhereValueBetween($endOfMonth->format('Y-m-d'), ['start_date', 'end_date'])
                )
                    ->where('status', AbsenceRequestStatusEnum::Approved),
            ])
            ->employed($endOfMonth)
            ->get()
            ->keyBy('id')
            ->map(function (Employee $employee) use ($calendarDays) {
                $employeeArray = $employee->toArray();
                $employeeDays = collect(Arr::pull($employeeArray, 'employee_days'))
                    ->keyBy(fn (array $day) => Carbon::parse(data_get($day, 'date'))->format('Y-m-d'))
                    ->toArray();
                $activeWorkTimes = collect(Arr::pull($employeeArray, 'work_times'))
                    ->keyBy(fn (array $workTime) => Carbon::parse(data_get($workTime, 'started_at'))->format('Y-m-d'))
                    ->toArray();
                $employeeArray['hours_percentage'] = percentage_of(
                    data_get($employeeArray, 'target_hours'),
                    data_get($employeeArray, 'actual_hours'),
                    0
                );
                $employeeArray['days_percentage'] = percentage_of(
                    data_get($employeeArray, 'target_days'),
                    data_get($employeeArray, 'actual_days'),
                    0
                );

                $employeeArray['days'] = collect($calendarDays)
                    ->merge($employeeDays)
                    ->merge($activeWorkTimes)
                    ->mapWithKeys(function (array $data, string $day) use ($employee) {
                        $parsedDay = Carbon::parse($day);
                        $dayData = CloseEmployeeDay::make()->calculateDayData($employee, $parsedDay);

                        if ($parsedDay->isFuture()) {
                            $dayData['plus_minus_overtime_hours'] = null;
                            $dayData['actual_hours'] = null;
                        }

                        return [
                            $day => array_merge($dayData->toArray(), $data),
                        ];
                    })
                    ->toArray();

                return $employeeArray;
            })
            ->groupBy('employee_department_id')
            ->toArray();
    }

    protected function loadHolidays(): void
    {
        $this->holidays = resolve_static(Location::class, 'query')
            ->with(['holidays' => fn (BelongsToMany $query) => $query->whereBetween(
                'date',
                [
                    Carbon::create($this->year, $this->month)->startOfMonth(),
                    Carbon::create($this->year, $this->month)->copy()->endOfMonth(),
                ]
            )
                ->select(['id', 'name', 'date']),
            ])
            ->get(['id', 'name'])
            ->keyBy('id')
            ->map(fn (Location $location) => $location
                ->holidays
                ->keyBy(fn (Holiday $holiday) => Carbon::parse($holiday->date)->format('Y-m-d'))
            )
            ->toArray();

        $this->holidays['no-location'] = [];
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
