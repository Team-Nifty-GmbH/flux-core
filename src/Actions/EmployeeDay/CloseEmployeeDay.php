<?php

namespace FluxErp\Actions\EmployeeDay;

use Carbon\Carbon;
use FluxErp\Actions\FluxAction;
use FluxErp\Enums\AbsenceRequestDayPartEnum;
use FluxErp\Enums\AbsenceRequestStateEnum;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\Employee;
use FluxErp\Models\EmployeeDay;
use FluxErp\Models\Holiday;
use FluxErp\Models\WorkTime;
use FluxErp\Rulesets\EmployeeDay\CloseEmployeeDayRuleset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class CloseEmployeeDay extends FluxAction
{
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
            ->where('state', AbsenceRequestStateEnum::Approved)
            ->with('absenceType:id,affects_overtime,affects_sick_leave,affects_vacation');

        $wasPresent = false;
        $actualHours = 0;
        $pauseTimes = 0;

        $sickLeave = $absenceRequestQuery->clone()
            ->whereRelation('absenceType', 'affects_sick_leave', true)
            ->get(['id', 'day_part'])
            ->reduce(
                function (?AbsenceRequestDayPartEnum $carry, AbsenceRequest $item): ?AbsenceRequestDayPartEnum {
                    if (
                        in_array(AbsenceRequestDayPartEnum::FullDay, [$carry, $item->day_part])
                        || ! array_diff(
                            [
                                AbsenceRequestDayPartEnum::FirstHalf,
                                AbsenceRequestDayPartEnum::SecondHalf,
                            ],
                            [$carry, $item->day_part]
                        )
                    ) {
                        return AbsenceRequestDayPartEnum::FullDay;
                    }

                    return $item->day_part;
                }
            );

        if ($sickLeave !== AbsenceRequestDayPartEnum::FullDay) {
            $workTimes = $workTimeQuery->clone()
                ->whereDate('started_at', $date)
                ->sum('total_time_ms');
            $pauseTimes = $workTimeQuery->clone()
                ->whereDate('started_at', $date)
                ->where('is_pause', true)
                ->sum('total_time_ms');
            $actualHours = bcdiv(bcadd($workTimes, $pauseTimes), 3600000);
            $wasPresent = bccomp($workTimes, 0) === 1;
        }

        $targetHours = 0;
        if ($isWorkDay = $employee->isWorkDay($date)) {
            $workTimeModel = $employee->getWorkTimeModel($date);
            $targetHours = $workTimeModel->getDailyWorkHours($date);
        }

        $data = [
            'sick_hours_used' => 0,
            'sick_days_used' => 0,
            'vacation_hours_used' => 0,
            'vacation_days_used' => 0,
            'overtime_used' => 0,
            'plus_minus_absence_hours' => 0,
        ];

        $usedAbsenceRequests = collect();

        if ($sickLeave === AbsenceRequestDayPartEnum::FullDay) {
            $absenceRequestQuery->whereRelation('absenceType', 'affects_vacation', false);
        }

        foreach ($absenceRequestQuery->get() as $absenceRequest) {
            if (data_get($absenceRequest, 'absenceType.affects_sick_leave')) {
                $days = $absenceRequest->calculateWorkDaysAffected($date);
                if (bccomp($days, 0) === 1) {
                    $data['sick_days_used'] = bcadd($data['sick_days_used'], $days);
                }

                $hours = $absenceRequest->calculateWorkHoursAffected($date);
                if (bccomp($hours, 0) === 1) {
                    $usedAbsenceRequests->push($absenceRequest);
                    $data['sick_hours_used'] = bcadd($data['sick_hours_used'], $hours);
                }
            } elseif (data_get($absenceRequest, 'absenceType.affects_vacation')) {
                if ($wasPresent
                    && $absenceRequest->day_part === AbsenceRequestDayPartEnum::FullDay
                    || $sickLeave === $absenceRequest->day_part
                ) {
                    continue;
                }

                $days = $absenceRequest->calculateWorkDaysAffected($date);
                if (bccomp($days, 0) === 1) {
                    $data['vacation_days_used'] = bcadd($data['vacation_days_used'], $days);
                }

                $hours = $absenceRequest->calculateWorkHoursAffected($date);
                if (bccomp($hours, 0) === 1) {
                    $usedAbsenceRequests->push($absenceRequest);
                    $data['vacation_hours_used'] = bcadd($data['vacation_hours_used'], $hours);
                }
            } elseif (data_get($absenceRequest, 'absenceType.affects_overtime')) {
                $hours = $absenceRequest->calculateWorkHoursAffected($date);
                if (bccomp($hours, 0) === 1) {
                    $usedAbsenceRequests->push($absenceRequest);
                    $data['overtime_used'] = bcadd($data['overtime_used'], $hours);
                }
            } else {
                $hours = $absenceRequest->calculateWorkHoursAffected($date);
                if (bccomp($hours, 0) === 1) {
                    $usedAbsenceRequests->push($absenceRequest);
                    $data['plus_minus_absence_hours'] = bcadd($data['plus_minus_absence_hours'], $hours);
                }
            }
        }

        // ((sick hours + vacation hours + absence hours) - target hours) + actual hours = overtime
        $totalOvertime = bcadd(
            bcsub(
                bcadd(
                    $data['sick_hours_used'],
                    bcadd(
                        $data['vacation_hours_used'],
                        $data['plus_minus_absence_hours']
                    )
                ),
                $targetHours
            ),
            $actualHours
        );

        $data = array_map(
            fn ($value) => bccomp($value, 0) === 1 ? bcround($value, 2) : 0,
            $data
        );

        $plusMinusOvertimeHours = bcsub($totalOvertime, Arr::pull($data, 'overtime_used'));

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
            $data,
            [
                'plus_minus_overtime_hours' => bcround($plusMinusOvertimeHours, 2),
                'is_holiday' => (bool) $holidayId,
                'is_work_day' => $isWorkDay,

                'absence_requests' => $usedAbsenceRequests,
                'work_times' => $workTimeQuery->clone()
                    ->whereDate('started_at', $date)
                    ->pluck('id'),
            ]
        ));
    }

    public static function models(): array
    {
        return [EmployeeDay::class];
    }

    protected function getRulesets(): string|array
    {
        return CloseEmployeeDayRuleset::class;
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
