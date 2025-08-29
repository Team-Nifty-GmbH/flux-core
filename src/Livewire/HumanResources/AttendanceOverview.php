<?php

namespace FluxErp\Livewire\HumanResources;

use FluxErp\Enums\AbsenceRequestStatusEnum;
use FluxErp\Livewire\DataTables\WorkTimeList;
use FluxErp\Models\AbsenceType;
use FluxErp\Models\Employee;
use FluxErp\Models\Holiday;
use FluxErp\Models\User;
use FluxErp\Models\WorkTime;
use FluxErp\Models\WorkTimeModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;

class AttendanceOverview extends Component
{
    public array $absenceTypes = [];

    public array $attendanceData = [];

    public array $calendarDays = [];

    public array $departments = [];

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
        $this->loadAbsenceTypes();
        $this->loadData();
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('flux::livewire.attendance-overview');
    }

    public function loadData(): void
    {
        $this->monthName = Carbon::create($this->year, $this->month)->locale('de')->monthName;
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
    public function showDetail(int $employeeId, string $date): void
    {
        $employee = resolve_static(Employee::class, 'query')
            ->whereKey($employeeId)
            ->first();
        $employeeName = $employee?->name ?? __('Unknown');

        if (strlen($date) === 7) {
            // Month view - show all work times for the month
            $startDate = Carbon::parse($date . '-01')->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();

            SessionFilter::make(
                Livewire::new(resolve_static(WorkTimeList::class, 'class'))->getCacheKey(),
                fn (Builder $query) => $query
                    ->where('employee_id', $employeeId)
                    ->whereBetween('started_at', [
                        $startDate->startOfDay(),
                        $endDate->endOfDay(),
                    ]),
                __('Work times for :user in :month', [
                    'user' => $employeeName,
                    'month' => $startDate->locale('de')->monthName . ' ' . $startDate->year,
                ])
            )->store();

            $this->redirectRoute('human-resources.work-times', navigate: true);

            return;
        }

        $dayData = null;
        $targetDate = Carbon::parse($date);
        $dayIndex = $targetDate->day;

        foreach ($this->attendanceData as $departmentId => $employees) {
            if (isset($employees[$employeeId])) {
                $dayData = data_get($employees[$employeeId], "days.{$dayIndex}");
                break;
            }
        }

        if (! $dayData) {
            return;
        }

        $employeeDayId = data_get($dayData, 'employeeDayId');

        if ($employeeDayId) {
            $this->redirectRoute('human-resources.employee-days.show', ['id' => $employeeDayId], navigate: true);
        } else {
            if (data_get($dayData, 'hasWorkTime')) {
                SessionFilter::make(
                    Livewire::new(resolve_static(WorkTimeList::class, 'class'))->getCacheKey(),
                    fn (Builder $query) => $query
                        ->where('employee_id', $employeeId)
                        ->whereDate('started_at', $date),
                    __('Work times for :user on :date', [
                        'user' => $employeeName,
                        'date' => $targetDate->format('d.m.Y'),
                    ])
                )->store();

                $this->redirectRoute('human-resources.work-times', navigate: true);
            }
        }
    }

    protected function calculateScheduledHours(?WorkTimeModel $workTimeModel, Carbon $date): float
    {
        if (! $workTimeModel || ! $workTimeModel->schedules || $workTimeModel->schedules->isEmpty()) {
            $isWeekend = $date->isWeekend();
            $defaultHours = $isWeekend ? 0 : 8;

            return $defaultHours;
        }

        $dayOfWeek = $date->dayOfWeek;
        $dbWeekday = $dayOfWeek === 0 ? 7 : $dayOfWeek;

        $maxWeek = $workTimeModel->schedules->max('week_number') ?: 1;
        $weekOfYear = $date->isoWeek;
        $cycleWeek = (($weekOfYear - 1) % $maxWeek) + 1;

        $daySchedule = $workTimeModel->schedules
            ->where('weekday', $dbWeekday)
            ->where('week_number', $cycleWeek)
            ->first();

        if (! $daySchedule || $daySchedule->work_hours <= 0) {
            return 0;
        }

        $grossHours = abs($daySchedule->work_hours);
        $breakHours = (data_get($daySchedule, 'break_minutes', 0)) / 60;

        return $grossHours - $breakHours;
    }

    protected function formatDayDisplay(array $day): array
    {
        $display = [
            'type' => 'none',
            'color' => '',
            'text' => '',
            'title' => '',
            'isClickable' => false,
            'isPartial' => false,
            'isRunning' => false,
            'isMixed' => false,
        ];

        $status = data_get($day, 'status');
        $absences = data_get($day, 'absences', []);
        $hasWorkTime = data_get($day, 'hasWorkTime', false);
        $actualWorkHours = data_get($day, 'actualWorkHours', 0);
        $isHoliday = data_get($day, 'isHoliday', false);
        $holidayName = data_get($day, 'holidayName');

        // Work has priority over holidays
        // Holidays have priority over everything else (absences, unexcused)
        if ($isHoliday && ! $hasWorkTime && $status !== 'present') {
            $display['type'] = 'holiday';
            $display['color'] = 'red';
            $display['text'] = '🎉';
            $display['title'] = $holidayName ?: __('Holiday');
            $display['isClickable'] = false;

            return $display;
        }

        // Check if it's a mixed day (work time + absences)
        $isMixed = $hasWorkTime && ! empty($absences);

        if ($status === 'present') {
            $display['type'] = 'present';
            $display['color'] = 'green';
            $display['isClickable'] = true;
            $display['isRunning'] = data_get($day, 'isRunning', false);

            if ($isMixed) {
                // Mixed display: Work + Absence(s)
                $display['isMixed'] = true;
                $textParts = [];
                $titleParts = [];

                // Add work time part
                if ($actualWorkHours > 0) {
                    $textParts[] = Number::format($actualWorkHours, 1) . 'h';
                    $titleParts[] = __('Work') . ': ' . Number::format($actualWorkHours, 1) . 'h';
                }

                // Add absence parts
                foreach ($absences as $absence) {
                    $absenceHours = data_get($absence, 'hours', 0);
                    if ($absenceHours > 0) {
                        $textParts[] = substr(data_get($absence, 'name', ''), 0, 1);
                        $titleParts[] = data_get($absence, 'name', '') . ': ' . Number::format($absenceHours, 1) . 'h';
                    }
                }

                $display['text'] = implode(' + ', $textParts);
                $display['title'] = implode(' / ', $titleParts);

                if ($display['isRunning']) {
                    $display['title'] .= ' (' . __('Running') . ')';
                }

                // Use primary color for work, but store absence colors for UI
                $display['absenceColors'] = array_column($absences, 'color');
            } else {
                // Pure work time
                if ($actualWorkHours > 0) {
                    $display['text'] = Number::format($actualWorkHours, 1) . 'h';
                    $display['title'] = __('Present') . ': ' . Number::format($actualWorkHours, 1) . 'h';
                    if ($display['isRunning']) {
                        $display['title'] .= ' (' . __('Running') . ')';
                    }
                }
            }
        } elseif ($status === 'absence_approved' && ! empty($absences)) {
            if (count($absences) > 1) {
                // Multiple absences
                $display['type'] = 'absence';
                $display['isMixed'] = true;

                $textParts = [];
                $titleParts = [];
                $colors = [];

                foreach ($absences as $absence) {
                    $textParts[] = substr(data_get($absence, 'name', ''), 0, 1);
                    $absenceHours = data_get($absence, 'hours', 0);
                    if ($absenceHours > 0) {
                        $titleParts[] = data_get($absence, 'name', '') . ': ' . Number::format($absenceHours, 1) . 'h';
                    } else {
                        $titleParts[] = data_get($absence, 'name', '');
                    }
                    $colors[] = data_get($absence, 'color', '');
                }

                $display['text'] = implode(' + ', $textParts);
                $display['title'] = implode(' / ', $titleParts);
                $display['color'] = count($colors) > 0 ? $colors[0] : '';
                $display['absenceColors'] = $colors;
            } else {
                // Single absence
                $absence = count($absences) > 0 ? $absences[0] : null;
                if ($absence) {
                    $display['type'] = 'absence';
                    $display['color'] = data_get($absence, 'color', '');
                    $display['text'] = substr(data_get($absence, 'name', ''), 0, 1);
                    $display['title'] = data_get($absence, 'name', '');

                    $absenceHours = data_get($absence, 'hours', 0);
                    if ($absenceHours > 0) {
                        $display['title'] .= ': ' . Number::format($absenceHours, 1) . 'h';
                    }
                }
            }

            $display['isClickable'] = true;
            $display['absenceTypeId'] = data_get($absences, '0.type_id');
        } elseif ($status === 'absent') {
            $display['type'] = 'absent';
            $display['color'] = 'red';
            $display['text'] = '!';
            $display['title'] = __('Unexcused absence');
        } elseif ($status === 'not_processed') {
            $display['type'] = 'not_processed';
            $display['color'] = 'orange';
            $display['text'] = '?';
            $display['title'] = __('Day not processed yet');
            $display['isClickable'] = true; // Make clickable to show detail
        }

        return $display;
    }

    protected function getWorkTimeModelForDate(Employee $employee, Carbon $date): ?WorkTimeModel
    {
        // Check if we already have the workTimeModelHistory loaded with the right schedules
        if ($employee->relationLoaded('workTimeModelHistory')) {
            $historicalModel = $employee->workTimeModelHistory
                ->first(function ($history) use ($date) {
                    $validFrom = Carbon::parse($history->valid_from);
                    $validUntil = $history->valid_until ? Carbon::parse($history->valid_until) : null;

                    return $date->gte($validFrom) && (! $validUntil || $date->lte($validUntil));
                });

            if ($historicalModel && $historicalModel->workTimeModel) {
                return $historicalModel->workTimeModel;
            }
        }

        // Fallback: Load fresh from database
        $historicalModel = $employee->workTimeModelHistory()
            ->where('valid_from', '<=', $date)
            ->where(function ($q) use ($date): void {
                $q->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', $date);
            })
            ->with('workTimeModel.schedules')
            ->first();

        if ($historicalModel && $historicalModel->workTimeModel) {
            return $historicalModel->workTimeModel;
        }

        // Get the User model to call currentWorkTimeModel
        if ($employee->user_id) {
            $user = resolve_static(User::class, 'query')
                ->with('currentWorkTimeModel.schedules')
                ->find($employee->user_id);
            if ($user) {
                return $user->getWorkTimeModel();
            }
        }

        return null;
    }

    protected function loadAbsenceTypes(): void
    {
        $this->absenceTypes = resolve_static(AbsenceType::class, 'query')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'color', 'percentage_deduction', 'affects_vacation', 'affects_overtime'])
            ->toArray();
    }

    protected function loadAttendanceData(): void
    {
        $startOfMonth = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $daysInMonth = $startOfMonth->daysInMonth;

        // Load employees with their departments, location and employee days for the month
        $allEmployees = resolve_static(Employee::class, 'query')
            ->employed($endOfMonth)
            ->select(['id', 'user_id', 'employee_department_id', 'location_id', 'name', 'firstname', 'lastname', 'employment_date', 'termination_date'])
            ->with([
                'employeeDepartment' => function ($query): void {
                    $query->where('is_active', true)->select(['id', 'name']);
                },
                'employeeDays' => function ($q) use ($startOfMonth, $endOfMonth): void {
                    $q->whereBetween('date', [$startOfMonth, $endOfMonth])
                        ->with([
                            'absenceRequests.absenceType:id,name,color',
                            'workTimes' => function ($workTimeQuery): void {
                                $workTimeQuery->where('is_daily_work_time', true);
                            },
                        ]);
                },
            ])
            ->get();

        $this->attendanceData = [];
        $this->departments = [];

        $employeesByDepartment = $allEmployees->groupBy(function ($employee) {
            if ($employee->employee_department_id && $employee->employeeDepartment) {
                return $employee->employee_department_id;
            }

            return 'no-dept';
        });

        foreach ($employeesByDepartment as $departmentKey => $departmentEmployees) {
            if ($departmentKey === 'no-dept') {
                $this->departments[$departmentKey] = [
                    'id' => $departmentKey,
                    'name' => __('No Department'),
                    'userCount' => $departmentEmployees->count(),
                ];
            } else {
                $department = $departmentEmployees->first()->employeeDepartment;
                $this->departments[$departmentKey] = [
                    'id' => $departmentKey,
                    'name' => $department->name,
                    'userCount' => $departmentEmployees->count(),
                ];
            }

            $this->attendanceData[$departmentKey] = [];

            foreach ($departmentEmployees as $employee) {
                $userData = $this->processEmployeeData($employee, $daysInMonth);
                $this->attendanceData[$departmentKey][$employee->getKey()] = $userData;
            }
        }
    }

    protected function loadHolidays(): void
    {
        $startOfMonth = Carbon::create($this->year, $this->month)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // Load holidays with their locations
        $holidays = resolve_static(Holiday::class, 'query')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->with('locations')
            ->get();

        // Group holidays by location_id
        $this->holidays = [];
        foreach ($holidays as $holiday) {
            $dateString = Carbon::parse($holiday->date)->format('Y-m-d');
            foreach ($holiday->locations as $location) {
                $locationId = $location->getKey();
                if (! isset($this->holidays[$locationId])) {
                    $this->holidays[$locationId] = [];
                }
                $this->holidays[$locationId][$dateString] = $holiday->name;
            }
        }

        // Also add a 'no-location' key for employees without location
        // (they get no holidays)
        $this->holidays['no-location'] = [];
    }

    protected function prepareCalendarDays(): void
    {
        $startDate = Carbon::create($this->year, $this->month, 1);
        $daysInMonth = $startDate->daysInMonth;
        $this->calendarDays = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($this->year, $this->month, $day);
            $dateString = $date->format('Y-m-d');

            $this->calendarDays[] = [
                'day' => $day,
                'date' => $dateString,
                'weekDay' => substr(__($date->format('D')), 0, 2),
                'isWeekend' => $date->isWeekend(),

                'isToday' => $date->isToday(),
                'isFuture' => $date->isFuture(),
            ];
        }
    }

    protected function processEmployeeData(Employee $employee, int $daysInMonth): array
    {
        $employeeId = $employee->getKey();
        $startOfMonth = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // Get holidays for this employee's location
        $employeeLocationId = $employee->location_id ?? 'no-location';
        $locationHolidays = $this->holidays[$employeeLocationId] ?? [];

        // Use the already loaded employee days from the with() relationship
        $employeeDaysByDate = $employee->employeeDays->keyBy(function ($employeeDay) {
            return $employeeDay->date->format('Y-m-d');
        });

        // Also load absence requests for the month (including future ones)
        $absenceRequests = resolve_static(\FluxErp\Models\AbsenceRequest::class, 'query')
            ->where('employee_id', $employeeId)
            ->where('status', AbsenceRequestStatusEnum::Approved)
            ->where(function ($query) use ($startOfMonth, $endOfMonth): void {
                $query->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                    ->orWhereBetween('end_date', [$startOfMonth, $endOfMonth])
                    ->orWhere(function ($q) use ($startOfMonth, $endOfMonth): void {
                        $q->where('start_date', '<=', $startOfMonth)
                            ->where('end_date', '>=', $endOfMonth);
                    });
            })
            ->with('absenceType')
            ->get();

        // Also check for currently running work times
        $runningWorkTimes = resolve_static(WorkTime::class, 'query')
            ->where('employee_id', $employeeId)
            ->where('is_daily_work_time', true)
            ->whereNull('ended_at')
            ->where('is_locked', false)
            ->whereDate('started_at', now()->toDateString())
            ->exists();

        $userData = [
            'id' => $employeeId,
            'name' => $employee->name,
            'url' => $employee->getUrl(),
            'actual_days' => 0,
            'work_days' => 0,
            'target_hours' => 0,
            'actual_hours' => 0,
            'days' => [],
        ];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $currentDate = Carbon::create($this->year, $this->month, $day);
            $dateString = $currentDate->format('Y-m-d');

            $dayData = [
                'date' => $dateString,
                'status' => 'none',
                'shouldWork' => false,
                'scheduledWorkHours' => 0,
                'actualWorkHours' => 0,
                'hasWorkTime' => false,
                'isRunning' => false,
                'workTime' => null,
                'absence' => null,
                'isHoliday' => isset($locationHolidays[$dateString]),
                'holidayName' => $locationHolidays[$dateString] ?? null,
                'isWeekend' => $currentDate->isWeekend(),
                'display' => null,
            ];

            // Check employment dates
            if ($employee->employment_date && $currentDate->lt(Carbon::parse($employee->employment_date))) {
                $userData['days'][$day] = $dayData;

                continue;
            }

            if ($employee->termination_date && $currentDate->gt(Carbon::parse($employee->termination_date))) {
                $userData['days'][$day] = $dayData;

                continue;
            }

            // Get employee day data if it exists
            $employeeDay = $employeeDaysByDate->get($dateString);

            if ($employeeDay) {
                // EmployeeDay exists - use its data (has priority)
                $dayData['scheduledWorkHours'] = floatval($employeeDay->target_hours);
                $dayData['actualWorkHours'] = floatval($employeeDay->actual_hours);
                $dayData['shouldWork'] = $employeeDay->target_hours > 0 && ! $dayData['isHoliday'];
                $dayData['hasWorkTime'] = $employeeDay->workTimes->isNotEmpty();
                $dayData['employeeDayId'] = $employeeDay->getKey();

                // Check if any work times are still running (not locked)
                // For today, also check the live query result
                if ($currentDate->isToday() && $runningWorkTimes) {
                    $dayData['isRunning'] = true;
                } else {
                    $runningWorkTime = $employeeDay->workTimes->first(function ($workTime) {
                        return ! $workTime->is_locked && ! $workTime->ended_at;
                    });
                    $dayData['isRunning'] = $runningWorkTime !== null;
                }

                // Apply hierarchy logic: Sick > Vacation > Overtime > Other
                $allAbsences = [];
                $hasSick = false;
                $hasVacation = false;
                $hasOvertime = false;

                foreach ($employeeDay->absenceRequests as $absenceRequest) {
                    if ($absenceRequest->absenceType) {
                        // Calculate hours for this specific date
                        $hours = $absenceRequest->calculateWorkHoursAffected($currentDate);
                        $hours = bccomp($hours, $dayData['scheduledWorkHours']) > 0 ? $dayData['scheduledWorkHours'] : $hours;

                        $absenceData = [
                            'id' => $absenceRequest->getKey(),
                            'type_id' => $absenceRequest->absenceType->getKey(),
                            'name' => $absenceRequest->absenceType->name,
                            'color' => $absenceRequest->absenceType->color,
                            'hours' => $hours,
                            'affects_sick' => $absenceRequest->absenceType->affects_sick ?? false,
                            'affects_vacation' => $absenceRequest->absenceType->affects_vacation ?? false,
                            'affects_overtime' => $absenceRequest->absenceType->affects_overtime ?? false,
                        ];

                        $allAbsences[] = $absenceData;

                        if ($absenceData['affects_sick']) {
                            $hasSick = true;
                        }
                        if ($absenceData['affects_vacation']) {
                            $hasVacation = true;
                        }
                        if ($absenceData['affects_overtime']) {
                            $hasOvertime = true;
                        }
                    }
                }

                // Apply hierarchy: only show the highest priority absence
                // and combine multiple absences of the same priority
                $dayData['absences'] = [];

                if ($hasSick) {
                    // Combine all sick absences into one
                    $sickAbsences = array_filter($allAbsences, fn ($a) => $a['affects_sick']);
                    if (! empty($sickAbsences)) {
                        $firstSick = reset($sickAbsences);
                        $totalHours = array_sum(array_column($sickAbsences, 'hours'));
                        $firstSick['hours'] = $totalHours;
                        $dayData['absences'][] = $firstSick;
                    }
                } elseif ($hasVacation) {
                    // Combine all vacation absences into one
                    $vacationAbsences = array_filter($allAbsences, fn ($a) => $a['affects_vacation']);
                    if (! empty($vacationAbsences)) {
                        $firstVacation = reset($vacationAbsences);
                        $totalHours = array_sum(array_column($vacationAbsences, 'hours'));
                        $firstVacation['hours'] = $totalHours;
                        $dayData['absences'][] = $firstVacation;
                    }
                } elseif ($hasOvertime) {
                    // Combine all overtime absences into one
                    $overtimeAbsences = array_filter($allAbsences, fn ($a) => $a['affects_overtime']);
                    if (! empty($overtimeAbsences)) {
                        $firstOvertime = reset($overtimeAbsences);
                        $totalHours = array_sum(array_column($overtimeAbsences, 'hours'));
                        $firstOvertime['hours'] = $totalHours;
                        $dayData['absences'][] = $firstOvertime;
                    }
                } else {
                    // Show other absences (those that don't affect sick/vacation/overtime)
                    $otherAbsences = array_filter($allAbsences, fn ($a) => ! $a['affects_sick'] && ! $a['affects_vacation'] && ! $a['affects_overtime']);
                    foreach ($otherAbsences as $absence) {
                        $dayData['absences'][] = $absence;
                    }
                }

                // For backward compatibility, keep the first absence as 'absence'
                $absences = data_get($dayData, 'absences', []);
                $dayData['absence'] = count($absences) > 0 ? $absences[0] : null;
                if (data_get($dayData, 'absence')) {
                    $dayData['absence']['is_partial'] = data_get($dayData, 'hasWorkTime', false) || count($absences) > 1;
                }

                // Determine status based on data
                $shouldWork = data_get($dayData, 'shouldWork', false);
                $hasWorkTime = data_get($dayData, 'hasWorkTime', false);
                $actualWorkHours = data_get($dayData, 'actualWorkHours', 0);
                $absences = data_get($dayData, 'absences', []);

                if ($shouldWork) {
                    if ($hasWorkTime && $actualWorkHours > 0) {
                        $dayData['status'] = 'present';
                    } elseif (! empty($absences)) {
                        $dayData['status'] = 'absence_approved';
                    } else {
                        if (! $currentDate->isFuture()) {
                            $dayData['status'] = 'absent';
                        }
                    }
                } elseif (! empty($absences)) {
                    $dayData['status'] = 'absence_approved';
                }

                // Update summary stats for past and current days
                if (! $currentDate->isFuture() && $shouldWork) {
                    $userData['work_days']++;

                    // With new logic: target hours stay the same even with absences
                    // If someone works during vacation/sick, they get overtime
                    $baseTargetHours = data_get($dayData, 'scheduledWorkHours', 0);
                    $userData['target_hours'] = bcadd($userData['target_hours'], $baseTargetHours);

                    $status = data_get($dayData, 'status');
                    if ($status === 'present' || $status === 'absence_approved') {
                        $userData['actual_days']++;
                    }

                    // Always use actual hours from employee day calculation
                    $userData['actual_hours'] = bcadd($userData['actual_hours'], $actualWorkHours);
                }
            } else {
                // No EmployeeDay exists - check for absences (for future dates)
                // Check for absence requests on this date
                $dayAbsenceRequests = $absenceRequests->filter(function ($ar) use ($currentDate) {
                    return $currentDate->between($ar->start_date, $ar->end_date);
                });

                // Calculate scheduled hours from work time model
                $workTimeModel = $this->getWorkTimeModelForDate($employee, $currentDate);
                $dayData['scheduledWorkHours'] = $workTimeModel ? $this->calculateScheduledHours($workTimeModel, $currentDate) : 0;
                $dayData['shouldWork'] = $dayData['scheduledWorkHours'] > 0 && ! $dayData['isHoliday'];

                if (! $dayAbsenceRequests->isEmpty()) {
                    // Process absence requests for display
                    $allAbsences = [];
                    foreach ($dayAbsenceRequests as $absenceRequest) {
                        if ($absenceRequest->absenceType) {
                            $hours = $absenceRequest->calculateWorkHoursAffected($currentDate);
                            $allAbsences[] = [
                                'id' => $absenceRequest->getKey(),
                                'type_id' => $absenceRequest->absenceType->getKey(),
                                'name' => $absenceRequest->absenceType->name,
                                'color' => $absenceRequest->absenceType->color,
                                'hours' => $hours,
                                'affects_sick' => $absenceRequest->absenceType->affects_sick ?? false,
                                'affects_vacation' => $absenceRequest->absenceType->affects_vacation ?? false,
                                'affects_overtime' => $absenceRequest->absenceType->affects_overtime ?? false,
                            ];
                        }
                    }

                    // Apply hierarchy for display
                    $hasSick = collect($allAbsences)->contains('affects_sick', true);
                    $hasVacation = collect($allAbsences)->contains('affects_vacation', true);
                    $hasOvertime = collect($allAbsences)->contains('affects_overtime', true);

                    $dayData['absences'] = [];
                    if ($hasSick) {
                        $sickAbsences = array_filter($allAbsences, fn ($a) => $a['affects_sick']);
                        if (! empty($sickAbsences)) {
                            $firstSick = reset($sickAbsences);
                            $totalHours = array_sum(array_column($sickAbsences, 'hours'));
                            $firstSick['hours'] = $totalHours;
                            $dayData['absences'][] = $firstSick;
                        }
                    } elseif ($hasVacation) {
                        $vacationAbsences = array_filter($allAbsences, fn ($a) => $a['affects_vacation']);
                        if (! empty($vacationAbsences)) {
                            $firstVacation = reset($vacationAbsences);
                            $totalHours = array_sum(array_column($vacationAbsences, 'hours'));
                            $firstVacation['hours'] = $totalHours;
                            $dayData['absences'][] = $firstVacation;
                        }
                    } elseif ($hasOvertime) {
                        $overtimeAbsences = array_filter($allAbsences, fn ($a) => $a['affects_overtime']);
                        if (! empty($overtimeAbsences)) {
                            $firstOvertime = reset($overtimeAbsences);
                            $totalHours = array_sum(array_column($overtimeAbsences, 'hours'));
                            $firstOvertime['hours'] = $totalHours;
                            $dayData['absences'][] = $firstOvertime;
                        }
                    } else {
                        $otherAbsences = array_filter($allAbsences, fn ($a) => ! $a['affects_sick'] && ! $a['affects_vacation'] && ! $a['affects_overtime']);
                        foreach ($otherAbsences as $absence) {
                            $dayData['absences'][] = $absence;
                        }
                    }

                    $dayData['absence'] = count($dayData['absences']) > 0 ? $dayData['absences'][0] : null;
                    if (! empty($dayData['absences'])) {
                        $dayData['status'] = 'absence_approved';
                    }
                } elseif ($currentDate->isToday() && $runningWorkTimes) {
                    // Today with running work time but no EmployeeDay yet
                    $dayData['hasWorkTime'] = true;
                    $dayData['isRunning'] = true;
                    $dayData['status'] = 'present';

                    // Get actual hours from running work times
                    $actualHours = resolve_static(WorkTime::class, 'query')
                        ->where('employee_id', $employeeId)
                        ->where('is_daily_work_time', true)
                        ->whereDate('started_at', now()->toDateString())
                        ->sum('total_time_ms');
                    $dayData['actualWorkHours'] = bcdiv($actualHours, 3600000, 2);
                } elseif (! $currentDate->isFuture()) {
                    // Past date without EmployeeDay - mark as not processed
                    $dayData['status'] = 'not_processed';
                }
            }

            $dayData['display'] = $this->formatDayDisplay($dayData);
            $userData['days'][$day] = $dayData;
        }

        $userData['attendance_percentage'] = $userData['work_days'] > 0
            ? round(($userData['actual_days'] / $userData['work_days']) * 100)
            : 0;

        $userData['hours_percentage'] = $userData['target_hours'] > 0
            ? round(($userData['actual_hours'] / $userData['target_hours']) * 100)
            : 0;

        $userData['actual_hours_formatted'] = Number::format($userData['actual_hours'], 1);
        $userData['target_hours_formatted'] = Number::format($userData['target_hours'], 1);

        return $userData;
    }
}
