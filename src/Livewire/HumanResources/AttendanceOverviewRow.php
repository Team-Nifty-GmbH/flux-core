<?php

namespace FluxErp\Livewire\HumanResources;

use FluxErp\Actions\EmployeeDay\CloseEmployeeDay;
use FluxErp\Enums\AbsenceRequestStateEnum;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\Employee;
use FluxErp\Models\EmployeeDay;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;

#[Lazy]
class AttendanceOverviewRow extends Component
{
    public array $absenceTypes;

    public array $calendarDays;

    public int|string $departmentId;

    public array $employee = [];

    public int $employeeId;

    public int $month;

    public int $year;

    public function mount(): void
    {
        $this->loadEmployeeData();
    }

    public function render(): View
    {
        return view('flux::livewire.human-resources.attendance-overview-row');
    }

    public function placeholder(): View
    {
        return view('flux::livewire.human-resources.attendance-overview-row-placeholder', [
            'calendarDays' => $this->calendarDays,
            'departmentId' => $this->departmentId,
        ]);
    }

    #[Renderless]
    public function showAbsenceRequest(AbsenceRequest $absenceRequest): void
    {
        $this->redirect($absenceRequest->getUrl(), navigate: true);
    }

    #[Renderless]
    public function showEmployee(): void
    {
        $employee = resolve_static(Employee::class, 'query')->find($this->employeeId);
        if ($employee) {
            $this->redirect($employee->getUrl(), navigate: true);
        }
    }

    #[Renderless]
    public function showEmployeeDay(EmployeeDay $employeeDay): void
    {
        $this->redirect($employeeDay->getUrl(), navigate: true);
    }

    public function showWorkTime(?string $date = null): void
    {
        $employee = resolve_static(Employee::class, 'query')->find($this->employeeId);
        if (! $employee) {
            return;
        }

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

    protected function loadEmployeeData(): void
    {
        $startOfMonth = Carbon::create($this->year, $this->month);
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $calendarDays = array_fill_keys(array_keys($this->calendarDays), []);

        $employee = resolve_static(Employee::class, 'query')
            ->select([
                'id',
                'user_id',
                'employee_department_id',
                'location_id',
                'name',
                'employment_date',
                'termination_date',
            ])
            ->withSum(
                [
                    'employeeDays as target_hours' => fn (Builder $query) => $query
                        ->whereBetween('date', [$startOfMonth, $endOfMonth->endOfDay()])
                        ->whereDoesntHaveRelation(
                            'absenceRequests',
                            'state',
                            AbsenceRequestStateEnum::Approved
                        ),
                ],
                'target_hours'
            )
            ->withSum(
                [
                    'employeeDays as actual_hours' => fn (Builder $query) => $query
                        ->whereBetween('date', [$startOfMonth, $endOfMonth->endOfDay()]),
                ],
                'actual_hours'
            )
            ->withCount(
                [
                    'employeeDays as target_days' => fn (Builder $query) => $query
                        ->whereBetween('date', [$startOfMonth, $endOfMonth])
                        ->where('is_work_day', true)
                        ->whereDoesntHaveRelation(
                            'absenceRequests',
                            'state',
                            AbsenceRequestStateEnum::Approved
                        ),
                ],
                'target_days'
            )
            ->withCount(
                [
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
                            ->where('state', AbsenceRequestStateEnum::Approved)
                            ->select([
                                'id',
                                'employee_id',
                                'absence_type_id',
                                'start_date',
                                'end_date',
                                'state',
                                'day_part',
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
            ])
            ->find($this->employeeId);

        if (! $employee) {
            return;
        }

        $employeeArray = $employee->toArray();
        $employeeDays = collect(Arr::pull($employeeArray, 'employee_days'))
            ->keyBy(fn (array $day) => Carbon::parse(data_get($day, 'date'))->format('Y-m-d'))
            ->toArray();
        $activeWorkTimes = collect(Arr::pull($employeeArray, 'work_times'))
            ->keyBy(fn (array $workTime) => Carbon::parse(data_get($workTime, 'started_at'))
                ->format('Y-m-d')
            )
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
                $dayData = resolve_static(
                    CloseEmployeeDay::class,
                    'calculateDayData',
                    [
                        'employee' => $employee,
                        'date' => $parsedDay,
                    ]
                );

                if ($parsedDay->isFuture()) {
                    $dayData['plus_minus_overtime_hours'] = null;
                    $dayData['actual_hours'] = null;
                }

                return [
                    $day => array_merge($dayData->toArray(), $data),
                ];
            })
            ->toArray();

        $this->employee = $employeeArray;
    }
}
