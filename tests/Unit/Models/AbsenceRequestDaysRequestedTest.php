<?php

use Carbon\Carbon;
use FluxErp\Enums\AbsenceRequestDayPartEnum;
use FluxErp\Enums\AbsenceRequestStateEnum;
use FluxErp\Enums\DayPartEnum;
use FluxErp\Enums\EmployeeCanCreateEnum;
use FluxErp\Enums\OvertimeCompensationEnum;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\AbsenceType;
use FluxErp\Models\Employee;
use FluxErp\Models\Holiday;
use FluxErp\Models\Pivots\EmployeeWorkTimeModel;
use FluxErp\Models\WorkTimeModel;

beforeEach(function (): void {
    $this->workTimeModel = app(WorkTimeModel::class)->create([
        'name' => 'Standard 40h',
        'weekly_hours' => 40,
        'work_days_per_week' => 5,
        'annual_vacation_days' => 30,
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
        'annual_vacation_days' => 30,
    ]);

    $this->employee = $this->employee->fresh('workTimeModelHistory.workTimeModel');

    $this->vacationType = app(AbsenceType::class)->create([
        'name' => 'Urlaub',
        'code' => 'URL',
        'color' => '#22c55e',
        'employee_can_create' => EmployeeCanCreateEnum::Yes,
        'affects_overtime' => false,
        'affects_sick_leave' => false,
        'affects_vacation' => true,
        'is_active' => true,
    ]);
});

function createAbsenceRequest(Carbon $startDate, Carbon $endDate): AbsenceRequest
{
    return app(AbsenceRequest::class)->create([
        'employee_id' => test()->employee->getKey(),
        'absence_type_id' => test()->vacationType->getKey(),
        'start_date' => $startDate->format('Y-m-d'),
        'end_date' => $endDate->format('Y-m-d'),
        'day_part' => AbsenceRequestDayPartEnum::FullDay,
        'state' => AbsenceRequestStateEnum::Pending,
    ]);
}

test('days requested skips weekends', function (): void {
    $monday = Carbon::now()->addWeek()->startOfWeek();

    $absenceRequest = createAbsenceRequest($monday, $monday->copy()->addDays(10));

    expect($absenceRequest->days_requested)->toEqual(9);
});

test('days requested skips holidays', function (): void {
    $monday = Carbon::now()->addWeek()->startOfWeek();

    app(Holiday::class)->create([
        'name' => 'Testfeiertag',
        'date' => $monday->copy()->addDay()->format('Y-m-d'),
        'day_part' => DayPartEnum::FullDay,
        'is_active' => true,
        'is_recurring' => false,
    ]);

    $absenceRequest = createAbsenceRequest($monday, $monday->copy()->addDays(4));

    expect($absenceRequest->days_requested)->toEqual(4);
});

test('days requested counts a single work day', function (): void {
    $monday = Carbon::now()->addWeek()->startOfWeek();

    $absenceRequest = createAbsenceRequest($monday, $monday);

    expect($absenceRequest->days_requested)->toEqual(1);
});
