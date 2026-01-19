<?php

use Carbon\Carbon;
use FluxErp\Actions\EmployeeDay\CloseEmployeeDay;
use FluxErp\Enums\AbsenceRequestDayPartEnum;
use FluxErp\Enums\AbsenceRequestStateEnum;
use FluxErp\Enums\EmployeeCanCreateEnum;
use FluxErp\Enums\OvertimeCompensationEnum;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\AbsenceType;
use FluxErp\Models\Employee;
use FluxErp\Models\Pivots\EmployeeWorkTimeModel;
use FluxErp\Models\WorkTimeModel;

beforeEach(function (): void {
    // Create a work time model with 8 hours per day, 5 days per week
    $this->workTimeModel = app(WorkTimeModel::class)->create([
        'name' => 'Standard 40h',
        'weekly_hours' => 40,
        'work_days_per_week' => 5,
        'annual_vacation_days' => 30,
        'overtime_compensation' => OvertimeCompensationEnum::TimeOff,
        'is_active' => true,
    ]);

    // Create employee
    $this->employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'is_active' => true,
    ]);

    // Link employee to work time model
    app(EmployeeWorkTimeModel::class)->create([
        'employee_id' => $this->employee->getKey(),
        'work_time_model_id' => $this->workTimeModel->getKey(),
        'valid_from' => now()->subYear(),
        'valid_until' => null,
        'annual_vacation_days' => 30,
    ]);

    // Reload employee with relationships
    $this->employee = $this->employee->fresh('workTimeModelHistory.workTimeModel');
});

test('overtime used is added to total overtime when affects_overtime is true', function (): void {
    // Create "Frei" absence type that uses overtime (affects_overtime = true)
    $absenceType = app(AbsenceType::class)->create([
        'name' => 'Frei',
        'code' => 'FRE',
        'color' => '#0891b2',
        'employee_can_create' => EmployeeCanCreateEnum::Yes,
        'affects_overtime' => true,
        'affects_sick_leave' => false,
        'affects_vacation' => false,
        'is_active' => true,
    ]);

    // Find the next Monday (a work day)
    $testDate = Carbon::now()->next(Carbon::MONDAY);

    // Create approved absence request for that day
    $absenceRequest = app(AbsenceRequest::class)->create([
        'employee_id' => $this->employee->getKey(),
        'absence_type_id' => $absenceType->getKey(),
        'start_date' => $testDate,
        'end_date' => $testDate,
        'day_part' => AbsenceRequestDayPartEnum::FullDay,
        'state' => AbsenceRequestStateEnum::Approved,
    ]);

    // Calculate day data using the action
    $dayData = CloseEmployeeDay::calculateDayData($this->employee, $testDate);

    // With 8 target hours and 0 actual hours:
    // totalOvertime = (0 + 0 + 0 - 8) + 0 = -8
    // overtime_used = 8 (from the absence request)
    // plusMinusOvertimeHours should be -8 + 8 = 0 (NOT -8 - 8 = -16)

    expect($dayData->get('target_hours'))->toBe('8.00');
    expect($dayData->get('actual_hours'))->toBe('0.00');
    // The key assertion: plus_minus_overtime_hours should be 0, not -16
    expect($dayData->get('plus_minus_overtime_hours'))->toBe('0.00');
});

test('overtime is negative when no absence compensates for missing work', function (): void {
    // Reload employee fresh to ensure relations are loaded
    $employee = resolve_static(Employee::class, 'query')
        ->whereKey($this->employee->getKey())
        ->with('workTimeModelHistory.workTimeModel')
        ->first();

    // Find the next Monday (a work day)
    $testDate = Carbon::now()->next(Carbon::MONDAY);

    // Ensure it's a work day
    expect($employee->isWorkDay($testDate))->toBeTrue();

    // Calculate day data without any absence request
    $dayData = CloseEmployeeDay::calculateDayData($employee, $testDate);

    // With 8 target hours and 0 actual hours (no work logged):
    // totalOvertime = (0 + 0 + 0 - 8) + 0 = -8
    // overtime_used = 0
    // plusMinusOvertimeHours = -8 + 0 = -8

    expect($dayData->get('target_hours'))->toBe('8.00');
    expect($dayData->get('actual_hours'))->toBe('0.00');
    expect($dayData->get('plus_minus_overtime_hours'))->toBe('-8.00');
});

test('pauses are not subtracted twice from actual hours', function (): void {
    // Reload employee fresh to ensure relations are loaded
    $employee = resolve_static(Employee::class, 'query')
        ->whereKey($this->employee->getKey())
        ->with('workTimeModelHistory.workTimeModel')
        ->first();

    // Find the next Monday (a work day)
    $testDate = Carbon::now()->next(Carbon::MONDAY);

    // Create main work time entry (10 hours, is_daily_work_time = true, is_pause = false)
    $employee->workTimes()->create([
        'started_at' => $testDate->copy()->setTime(6, 30),
        'ended_at' => $testDate->copy()->setTime(16, 30),
        'total_time_ms' => 10 * 3600000, // 10 hours in ms
        'paused_time_ms' => 0,
        'is_daily_work_time' => true,
        'is_pause' => false,
        'is_locked' => true,
    ]);

    // Create pause entries (is_daily_work_time = true, is_pause = true, negative total_time_ms)
    // Lunch break: 30 minutes
    $employee->workTimes()->create([
        'started_at' => $testDate->copy()->setTime(12, 0),
        'ended_at' => $testDate->copy()->setTime(12, 30),
        'total_time_ms' => -30 * 60000, // -30 minutes in ms
        'paused_time_ms' => 0,
        'is_daily_work_time' => true,
        'is_pause' => true,
        'is_locked' => true,
    ]);

    // Coffee break: 15 minutes
    $employee->workTimes()->create([
        'started_at' => $testDate->copy()->setTime(9, 0),
        'ended_at' => $testDate->copy()->setTime(9, 15),
        'total_time_ms' => -15 * 60000, // -15 minutes in ms
        'paused_time_ms' => 0,
        'is_daily_work_time' => true,
        'is_pause' => true,
        'is_locked' => true,
    ]);

    // Calculate day data
    $dayData = CloseEmployeeDay::calculateDayData($employee, $testDate);

    // Expected: 10h - 45min pause = 9.25h actual hours
    // Before fix: pauses were subtracted twice, resulting in 10h - 45min - 45min = 8.5h
    expect($dayData->get('target_hours'))->toBe('8.00');
    expect($dayData->get('actual_hours'))->toBe('9.25');
    // 9.25h actual - 8h target = +1.25h overtime
    expect($dayData->get('plus_minus_overtime_hours'))->toBe('1.25');
});
