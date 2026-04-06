<?php

use FluxErp\Actions\Employee\CreateEmployee;
use FluxErp\Actions\EmployeeWorkTimeModel\CreateEmployeeWorkTimeModel;
use FluxErp\Actions\WorkTimeModel\CreateWorkTimeModel;

beforeEach(function (): void {
    $this->wtm = CreateWorkTimeModel::make([
        'name' => 'Standard',
        'cycle_weeks' => 1,
        'weekly_hours' => 40,
        'annual_vacation_days' => 30,
        'overtime_compensation' => 'payment',
        'is_active' => true,
    ])->validate()->execute();

    $this->employee = CreateEmployee::make([
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'employment_date' => '2026-01-01',
        'tenant_id' => $this->dbTenant->getKey(),
        'work_time_model_id' => $this->wtm->getKey(),
    ])->validate()->execute();
});

test('create employee work time model', function (): void {
    $wtm2 = CreateWorkTimeModel::make([
        'name' => 'Part Time',
        'cycle_weeks' => 1,
        'weekly_hours' => 20,
        'annual_vacation_days' => 15,
        'overtime_compensation' => 'time_off',
        'is_active' => true,
    ])->validate()->execute();

    $ewtm = CreateEmployeeWorkTimeModel::make([
        'employee_id' => $this->employee->getKey(),
        'work_time_model_id' => $wtm2->getKey(),
        'valid_from' => '2026-07-01',
    ])->validate()->execute();

    expect($ewtm)->employee_id->toBe($this->employee->getKey());
});

test('create employee work time model requires employee work_time_model valid_from', function (): void {
    CreateEmployeeWorkTimeModel::assertValidationErrors([], [
        'employee_id', 'work_time_model_id', 'valid_from',
    ]);
});

test('create employee work time model requires fields', function (): void {
    CreateEmployeeWorkTimeModel::assertValidationErrors([], [
        'employee_id', 'work_time_model_id', 'valid_from',
    ]);
});
