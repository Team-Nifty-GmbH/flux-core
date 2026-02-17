<?php

use Carbon\Carbon;
use FluxErp\Actions\AbsenceRequest\CreateAbsenceRequest;
use FluxErp\Enums\AbsenceRequestDayPartEnum;
use FluxErp\Enums\AbsenceRequestStateEnum;
use FluxErp\Enums\EmployeeCanCreateEnum;
use FluxErp\Enums\OvertimeCompensationEnum;
use FluxErp\Models\AbsenceType;
use FluxErp\Models\Employee;
use FluxErp\Models\EmployeeDay;
use FluxErp\Models\Pivots\EmployeeWorkTimeModel;
use FluxErp\Models\WorkTimeModel;

beforeEach(function (): void {
    $this->workTimeModel = app(WorkTimeModel::class)->create([
        'name' => 'Standard 40h',
        'weekly_hours' => 40,
        'work_days_per_week' => 5,
        'annual_vacation_days' => 24,
        'overtime_compensation' => OvertimeCompensationEnum::TimeOff,
        'is_active' => true,
    ]);

    $this->employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'is_active' => true,
    ]);

    app(EmployeeWorkTimeModel::class)->create([
        'employee_id' => $this->employee->getKey(),
        'work_time_model_id' => $this->workTimeModel->getKey(),
        'valid_from' => now()->subYear(),
        'valid_until' => null,
        'annual_vacation_days' => 24,
    ]);

    $this->employee = $this->employee->fresh('workTimeModelHistory.workTimeModel');

    $this->sickType = app(AbsenceType::class)->create([
        'name' => 'Krank',
        'code' => 'KRK',
        'color' => '#ef4444',
        'employee_can_create' => EmployeeCanCreateEnum::Yes,
        'affects_overtime' => false,
        'affects_sick_leave' => true,
        'affects_vacation' => false,
        'is_active' => true,
    ]);
});

test('creating absence request with approved state triggers employee day calculation', function (): void {
    $testDate = Carbon::now()->next(Carbon::MONDAY);

    $absenceRequest = CreateAbsenceRequest::make([
        'employee_id' => $this->employee->getKey(),
        'absence_type_id' => $this->sickType->getKey(),
        'start_date' => $testDate->format('Y-m-d'),
        'end_date' => $testDate->format('Y-m-d'),
        'day_part' => AbsenceRequestDayPartEnum::FullDay,
        'state' => AbsenceRequestStateEnum::Approved->value,
    ])->validate()->execute();

    $employeeDay = EmployeeDay::query()
        ->where('employee_id', $this->employee->getKey())
        ->where('date', $testDate->format('Y-m-d'))
        ->first();

    expect($employeeDay)->not->toBeNull();
    expect($employeeDay->sick_days_used)->toBe('1.00');
    expect($absenceRequest->employeeDays()->count())->toBe(1);
});

test('creating multi day absence request with approved state creates all employee days', function (): void {
    $startDate = Carbon::now()->next(Carbon::MONDAY);
    $endDate = $startDate->copy()->addDays(11); // Mon to Fri of next week (2 full work weeks)

    $absenceRequest = CreateAbsenceRequest::make([
        'employee_id' => $this->employee->getKey(),
        'absence_type_id' => $this->sickType->getKey(),
        'start_date' => $startDate->format('Y-m-d'),
        'end_date' => $endDate->format('Y-m-d'),
        'day_part' => AbsenceRequestDayPartEnum::FullDay,
        'state' => AbsenceRequestStateEnum::Approved->value,
    ])->validate()->execute();

    $employeeDays = EmployeeDay::query()
        ->where('employee_id', $this->employee->getKey())
        ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
        ->where('is_work_day', true)
        ->get();

    // 2 weeks * 5 work days = 10 work days
    expect($employeeDays)->toHaveCount(10);
    expect($employeeDays->sum('sick_days_used'))->toBe(10.0);
    expect($absenceRequest->employeeDays()->count())->toBe(10);
});
