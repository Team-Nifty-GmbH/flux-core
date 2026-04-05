<?php

use FluxErp\Actions\AbsenceRequest\CreateAbsenceRequest;
use FluxErp\Actions\AbsenceRequest\DeleteAbsenceRequest;
use FluxErp\Actions\AbsenceType\CreateAbsenceType;
use FluxErp\Actions\Employee\CreateEmployee;
use FluxErp\Actions\WorkTimeModel\CreateWorkTimeModel;

beforeEach(function (): void {
    $wtm = CreateWorkTimeModel::make([
        'name' => 'Standard',
        'cycle_weeks' => 1,
        'weekly_hours' => 40,
        'annual_vacation_days' => 30,
        'overtime_compensation' => 'payment',
        'is_active' => true,
    ])->validate()->execute();

    $this->employee = CreateEmployee::make([
        'firstname' => 'John',
        'lastname' => 'Doe',
        'employment_date' => '2025-01-01',
        'tenant_id' => $this->dbTenant->getKey(),
        'work_time_model_id' => $wtm->getKey(),
    ])->validate()->execute();

    $this->absenceType = CreateAbsenceType::make([
        'name' => 'Vacation',
        'code' => 'VAC',
        'color' => '#00FF00',
        'percentage_deduction' => 1.0,
        'employee_can_create' => 'yes',
        'affects_vacation' => true,
        'affects_overtime' => false,
        'affects_sick_leave' => false,
    ])->validate()->execute();
});

test('create absence request', function (): void {
    $request = CreateAbsenceRequest::make([
        'absence_type_id' => $this->absenceType->getKey(),
        'employee_id' => $this->employee->getKey(),
        'day_part' => 'full_day',
        'start_date' => '2026-07-01',
        'end_date' => '2026-07-05',
        'state' => 'pending',
    ])->validate()->execute();

    expect($request)
        ->employee_id->toBe($this->employee->getKey())
        ->absence_type_id->toBe($this->absenceType->getKey());
});

test('create absence request requires absence_type employee day_part dates', function (): void {
    CreateAbsenceRequest::assertValidationErrors([], [
        'absence_type_id', 'employee_id', 'day_part', 'start_date', 'end_date',
    ]);
});

test('delete absence request', function (): void {
    $request = CreateAbsenceRequest::make([
        'absence_type_id' => $this->absenceType->getKey(),
        'employee_id' => $this->employee->getKey(),
        'day_part' => 'full_day',
        'start_date' => '2026-08-01',
        'end_date' => '2026-08-01',
        'state' => 'pending',
    ])->validate()->execute();

    expect(DeleteAbsenceRequest::make(['id' => $request->getKey()])
        ->validate()->execute())->toBeTrue();
});
