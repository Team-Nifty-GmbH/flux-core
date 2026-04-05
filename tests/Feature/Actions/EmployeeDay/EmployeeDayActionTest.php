<?php

use FluxErp\Actions\Employee\CreateEmployee;
use FluxErp\Actions\EmployeeDay\CreateEmployeeDay;
use FluxErp\Actions\EmployeeDay\DeleteEmployeeDay;
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
        'firstname' => 'Test',
        'lastname' => 'Worker',
        'employment_date' => '2025-01-01',
        'tenant_id' => $this->dbTenant->getKey(),
        'work_time_model_id' => $wtm->getKey(),
    ])->validate()->execute();
});

test('create employee day', function (): void {
    $day = CreateEmployeeDay::make([
        'employee_id' => $this->employee->getKey(),
        'date' => '2026-04-05',
        'target_hours' => 8,
        'actual_hours' => 0,
    ])->validate()->execute();

    expect($day)
        ->employee_id->toBe($this->employee->getKey())
        ->date->not->toBeNull();
});

test('create employee day requires employee_id and date', function (): void {
    CreateEmployeeDay::assertValidationErrors([], ['employee_id', 'date']);
});

test('delete employee day', function (): void {
    $day = CreateEmployeeDay::make([
        'employee_id' => $this->employee->getKey(),
        'date' => '2026-04-06',
        'target_hours' => 8,
        'actual_hours' => 0,
    ])->validate()->execute();

    expect(DeleteEmployeeDay::make(['id' => $day->getKey()])
        ->validate()->execute())->toBeTrue();
});
