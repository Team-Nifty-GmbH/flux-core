<?php

namespace FluxErp\Actions\EmployeeDay;

use Carbon\Carbon;
use FluxErp\Actions\FluxAction;
use FluxErp\Enums\AbsenceRequestStatusEnum;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\Employee;
use FluxErp\Models\EmployeeDay;
use FluxErp\Models\WorkTime;
use FluxErp\Rulesets\EmployeeDay\CloseEmployeeDayRuleset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

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

    public function calculateDayData(Employee $employee, Carbon $date): array
    {
        /** @var Builder $baseQuery */
        $baseQuery = resolve_static(WorkTime::class, 'query')
            ->where(function (Builder $query) use ($employee): void {
                $query->where('employee_id', $employee->getKey());
            })
            ->where('is_daily_work_time', true);

        $workTimes = $baseQuery->clone()
            ->whereDate('started_at', $date)
            ->sum('total_time_ms');
        $pauseTimes = $baseQuery->clone()
            ->whereDate('started_at', $date)
            ->where('is_pause', true)
            ->sum('total_time_ms');
        $actualHours = bcdiv(bcadd($workTimes, $pauseTimes), 3600000);

        $absenceRequestBaseQuery = resolve_static(AbsenceRequest::class, 'query')
            ->where('employee_id', $employee->getKey())
            ->whereValueBetween($date, ['start_date', 'end_date'])
            ->where('status', AbsenceRequestStatusEnum::Approved)
            ->with('absenceType');

        $absenceRequests = $absenceRequestBaseQuery->get();

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
        $usedAbsenceRequestIds = [];

        if (bccomp($actualHours, 0) > 0) {
            foreach ($absenceRequests as $absenceRequest) {
                $usedAbsenceRequestIds[] = $absenceRequest->getKey();
            }
        } else {
            $absenceProcessed = false;

            foreach ($absenceRequests as $absenceRequest) {
                if (data_get($absenceRequest, 'absenceType.affects_sick')) {
                    $hours = $absenceRequest->calculateWorkHoursAffected($date);
                    if (bccomp($hours, 0) > 0) {
                        $usedAbsenceRequestIds[] = $absenceRequest->getKey();
                        $hoursUsed['sick_hours_used'] = bcadd(
                            data_get($hoursUsed, 'sick_hours_used', 0),
                            $hours
                        );
                        $absenceProcessed = true;

                        break;
                    }
                }
            }

            if (! $absenceProcessed) {
                foreach ($absenceRequests as $absenceRequest) {
                    if (data_get($absenceRequest, 'absenceType.affects_vacation')) {
                        $hours = $absenceRequest->calculateWorkHoursAffected($date);
                        if (bccomp($hours, 0) > 0) {
                            $usedAbsenceRequestIds[] = $absenceRequest->getKey();
                            $hoursUsed['vacation_hours_used'] = bcadd(
                                data_get($hoursUsed, 'vacation_hours_used', 0),
                                $hours
                            );
                            $absenceProcessed = true;

                            break;
                        }
                    }
                }
            }

            if (! $absenceProcessed) {
                foreach ($absenceRequests as $absenceRequest) {
                    if (data_get($absenceRequest, 'absenceType.affects_overtime')) {
                        $hours = $absenceRequest->calculateWorkHoursAffected($date);
                        if (bccomp($hours, 0) > 0) {
                            $usedAbsenceRequestIds[] = $absenceRequest->getKey();
                            $hoursUsed['overtime_used'] = bcadd(
                                data_get($hoursUsed, 'overtime_used', 0),
                                $hours
                            );
                            $absenceProcessed = true;

                            break;
                        }
                    }
                }
            }

            if (! $absenceProcessed) {
                foreach ($absenceRequests as $absenceRequest) {
                    if (! data_get($absenceRequest, 'absenceType.affects_sick') &&
                        ! data_get($absenceRequest, 'absenceType.affects_vacation') &&
                        ! data_get($absenceRequest, 'absenceType.affects_overtime')) {
                        $hours = $absenceRequest->calculateWorkHoursAffected($date);
                        if (bccomp($hours, 0) > 0) {
                            $usedAbsenceRequestIds[] = $absenceRequest->getKey();
                            $hoursUsed['plus_minus_absence_hours'] = bcadd(
                                data_get($hoursUsed, 'plus_minus_absence_hours', 0),
                                $hours
                            );

                            break;
                        }
                    }
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

        return array_merge(
            [
                'target_hours' => $targetHours,
                'actual_hours' => bcround($actualHours, 2),
                'break_minutes' => bcround(bcdiv($pauseTimes, 60000)),
            ],
            $formattedHours,
            [
                'absence_requests' => $usedAbsenceRequestIds,
                'work_times' => $baseQuery->clone()
                    ->whereDate('started_at', $date)
                    ->pluck('id'),
                'is_work_day' => $isWorkDay,
                'is_holiday' => $employee->location?->isHoliday($date),
            ]
        );
    }

    public function performAction(): EmployeeDay
    {
        /** @var Employee $employee */
        $employee = resolve_static(Employee::class, 'query')
            ->with(['workTimeModelHistory.workTimeModel.schedules'])
            ->whereKey($this->getData('employee_id'))
            ->first();

        $date = Carbon::parse($this->getData('date'));

        $employeeDay = resolve_static(EmployeeDay::class, 'query')
            ->where('employee_id', $employee->getKey())
            ->whereDate('date', $date)
            ->first();

        $dayData = $this->calculateDayData($employee, $date);
        $workTimes = Arr::pull($dayData, 'work_times', []);
        $absenceRequests = Arr::pull($dayData, 'absence_requests', []);

        if ($employeeDay) {
            $employeeDay->update($dayData);
        } else {
            $employeeDay = app(
                EmployeeDay::class,
                [
                    'attributes' => array_merge(
                        $dayData,
                        [
                            'employee_id' => $employee->getKey(),
                            'date' => $date,
                        ],
                    ),
                ]
            );

            $employeeDay->save();
        }

        $employeeDay->workTimes()->sync($workTimes);
        $employeeDay->absenceRequests()->sync($absenceRequests);

        return $employeeDay->fresh();
    }
}
