<?php

namespace FluxErp\Actions\EmployeeDay;

use Carbon\Carbon;
use FluxErp\Actions\FluxAction;
use FluxErp\Enums\AbsenceRequestStateEnum;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\Employee;
use FluxErp\Models\EmployeeDay;
use FluxErp\Models\Holiday;
use FluxErp\Models\WorkTime;
use FluxErp\Rulesets\EmployeeDay\CloseEmployeeDayRuleset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CloseEmployeeDay extends FluxAction
{
    public static function models(): array
    {
        return [EmployeeDay::class];
    }

    protected function getRulesets(): string|array
    {
        return CloseEmployeeDayRuleset::class;
    }

    public static function calculateDayData(Employee $employee, Carbon $date): Collection
    {
        /** @var Builder $workTimeQuery */
        $workTimeQuery = resolve_static(WorkTime::class, 'query')
            ->where(function (Builder $query) use ($employee): void {
                $query->where('employee_id', $employee->getKey());
            })
            ->where('is_daily_work_time', true);

        $absenceRequestQuery = resolve_static(AbsenceRequest::class, 'query')
            ->where('employee_id', $employee->getKey())
            ->whereValueBetween($date, ['start_date', 'end_date'])
            ->where('state_enum', AbsenceRequestStateEnum::Approved)
            ->with('absenceType:id,affects_overtime,affects_sick_leave,affects_vacation');

        $wasPresent = false;
        $actualHours = 0;
        $pauseTimes = 0;
        $wasSick = $absenceRequestQuery->clone()
            ->whereRelation('absenceType', 'affects_sick_leave', true)
            ->exists();

        if (! $wasSick) {
            $workTimes = $workTimeQuery->clone()
                ->whereDate('started_at', $date)
                ->sum('total_time_ms');
            $pauseTimes = $workTimeQuery->clone()
                ->whereDate('started_at', $date)
                ->where('is_pause', true)
                ->sum('total_time_ms');
            $actualHours = bcdiv(bcadd($workTimes, $pauseTimes), 3600000);
            $wasPresent = bccomp($workTimes, 0) > 0;
        }

        $workTimeModel = $employee->getWorkTimeModel($date);
        $targetHours = 0;

        if ($isWorkDay = $employee->isWorkDay($date)) {
            $targetHours = $workTimeModel->getDailyWorkHours($date);
        }

        $hoursUsed = [
            'sick_hours_used' => 0,
            'vacation_hours_used' => 0,
            'overtime_used' => 0,
            'plus_minus_absence_hours' => 0,
        ];
        $usedAbsenceRequests = collect();

        if ($wasSick || $wasPresent) {
            $absenceRequestQuery->whereRelation('absenceType', 'affects_vacation', false);
        }

        foreach ($absenceRequestQuery->get() as $absenceRequest) {
            if (data_get($absenceRequest, 'absenceType.affects_sick_leave')) {
                $hours = $absenceRequest->calculateWorkHoursAffected($date);
                if (bccomp($hours, 0) > 0) {
                    $usedAbsenceRequests->push($absenceRequest);
                    $hoursUsed['sick_hours_used'] = bcadd(
                        data_get($hoursUsed, 'sick_hours_used', 0),
                        $hours
                    );
                }
            } elseif (data_get($absenceRequest, 'absenceType.affects_vacation')) {
                $hours = $absenceRequest->calculateWorkHoursAffected($date);
                if (bccomp($hours, 0) > 0) {
                    $usedAbsenceRequests->push($absenceRequest);
                    $hoursUsed['vacation_hours_used'] = bcadd(
                        data_get($hoursUsed, 'vacation_hours_used', 0),
                        $hours
                    );
                }
            } elseif (data_get($absenceRequest, 'absenceType.affects_overtime')) {
                $hours = $absenceRequest->calculateWorkHoursAffected($date);
                if (bccomp($hours, 0) > 0) {
                    $usedAbsenceRequests->push($absenceRequest);
                    $hoursUsed['overtime_used'] = bcadd(
                        data_get($hoursUsed, 'overtime_used', 0),
                        $hours
                    );
                }
            } else {
                $hours = $absenceRequest->calculateWorkHoursAffected($date);
                if (bccomp($hours, 0) > 0) {
                    $usedAbsenceRequests->push($absenceRequest);
                    $hoursUsed['plus_minus_absence_hours'] = bcadd(
                        data_get($hoursUsed, 'plus_minus_absence_hours', 0),
                        $hours
                    );
                }
            }
        }

        $totalAbsenceHours = bcadd(
            data_get($hoursUsed, 'sick_hours_used', 0),
            data_get($hoursUsed, 'vacation_hours_used', 0)
        );

        $overtimeFromWorkingWhileAbsent = 0;
        if (bccomp($totalAbsenceHours, 0) > 0 && bccomp($actualHours, 0) > 0) {
            $overtimeFromWorkingWhileAbsent = $actualHours;
        }

        $regularOvertime = 0;
        if (bccomp($totalAbsenceHours, 0) == 0) {
            $regularOvertime = bcsub($actualHours, $targetHours);
        }

        $totalOvertime = bccomp($overtimeFromWorkingWhileAbsent, 0) > 0
            ? $overtimeFromWorkingWhileAbsent
            : $regularOvertime;

        $plusMinusOvertimeHours = bcsub($totalOvertime, data_get($hoursUsed, 'overtime_used', 0));

        $vacationHoursUsed = data_get($hoursUsed, 'vacation_hours_used', 0) > 0
                ? bcround(bcmul(data_get($hoursUsed, 'vacation_hours_used'), -1), 2)
                : 0;
        $sickHoursUsed = data_get($hoursUsed, 'sick_hours_used', 0) > 0
                ? bcround(bcmul(data_get($hoursUsed, 'sick_hours_used'), -1), 2)
                : 0;

        $formattedHours = [
            'sick_hours_used' => $sickHoursUsed,
            'sick_days_used' => $targetHours
                ? bcround(
                    bcdiv(
                        $sickHoursUsed,
                        $targetHours
                    ),
                    2
                )
                : 0,
            'vacation_hours_used' => $vacationHoursUsed,
            'vacation_days_used' => $targetHours
                ? bcround(
                    bcdiv(
                        $vacationHoursUsed,
                        $targetHours
                    ),
                    2
                )
                : 0,
            'plus_minus_overtime_hours' => bcround($plusMinusOvertimeHours, 2),
            'plus_minus_absence_hours' => bcround(data_get($hoursUsed, 'plus_minus_absence_hours', 0), 2),
        ];

        $holidayId = resolve_static(Holiday::class, 'query')
            ->isHoliday($date, $employee->location_id)
            ->value('id');

        return collect(array_merge(
            [
                'holiday_id' => $holidayId,
                'target_hours' => $targetHours,
                'actual_hours' => bcround($actualHours, 2),
                'break_minutes' => bcround(bcdiv($pauseTimes, 60000)),
            ],
            $formattedHours,
            [
                'is_holiday' => (bool) $holidayId,
                'is_work_day' => $isWorkDay,
                'absence_requests' => $usedAbsenceRequests,
                'work_times' => $workTimeQuery->clone()
                    ->whereDate('started_at', $date)
                    ->pluck('id'),
            ]
        ));
    }

    public function performAction(): EmployeeDay
    {
        /** @var Employee $employee */
        $employee = resolve_static(Employee::class, 'query')
            ->whereKey($this->getData('employee_id'))
            ->with(['workTimeModelHistory.workTimeModel.schedules'])
            ->first();

        $date = Carbon::parse($this->getData('date'));

        $employeeDay = resolve_static(EmployeeDay::class, 'query')
            ->where('employee_id', $employee->getKey())
            ->whereDate('date', $date)
            ->first();

        $dayData = static::calculateDayData($employee, $date);
        $workTimes = $dayData->pull('work_times');
        $absenceRequests = $dayData->pull('absence_requests');

        if ($employeeDay) {
            $employeeDay->fill($dayData->toArray());
        } else {
            $employeeDay = app(
                EmployeeDay::class,
                [
                    'attributes' => array_merge(
                        $dayData->toArray(),
                        [
                            'employee_id' => $employee->getKey(),
                            'date' => $date,
                        ],
                    ),
                ]
            );
        }

        $employeeDay->save();

        $employeeDay->workTimes()->sync($workTimes);
        $employeeDay->absenceRequests()->sync($absenceRequests);

        return $employeeDay->withoutRelations()->fresh();
    }
}
