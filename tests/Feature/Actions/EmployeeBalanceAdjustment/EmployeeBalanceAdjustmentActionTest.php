<?php

use FluxErp\Actions\Employee\CreateEmployee;
use FluxErp\Actions\EmployeeBalanceAdjustment\CreateEmployeeBalanceAdjustment;
use FluxErp\Actions\EmployeeBalanceAdjustment\DeleteEmployeeBalanceAdjustment;
use FluxErp\Actions\EmployeeBalanceAdjustment\UpdateEmployeeBalanceAdjustment;
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
        'lastname' => 'Employee',
        'employment_date' => '2026-01-01',
        'tenant_id' => $this->dbTenant->getKey(),
        'work_time_model_id' => $wtm->getKey(),
    ])->validate()->execute();
});

test('create employee balance adjustment', function (): void {
    $adj = CreateEmployeeBalanceAdjustment::make([
        'employee_id' => $this->employee->getKey(),
        'type' => 'vacation',
        'amount' => 5.0,
        'effective_date' => '2026-06-01',
        'reason' => 'correction',
    ])->validate()->execute();

    expect($adj)
        ->employee_id->toBe($this->employee->getKey())
        ->not->toBeNull();
});

test('create employee balance adjustment requires all fields', function (): void {
    CreateEmployeeBalanceAdjustment::assertValidationErrors([], [
        'employee_id', 'type', 'amount', 'effective_date', 'reason',
    ]);
});

test('update employee balance adjustment', function (): void {
    $adj = CreateEmployeeBalanceAdjustment::make([
        'employee_id' => $this->employee->getKey(),
        'type' => 'vacation',
        'amount' => 3.0,
        'effective_date' => '2026-06-01',
        'reason' => 'carryover',
    ])->validate()->execute();

    $updated = UpdateEmployeeBalanceAdjustment::make([
        'id' => $adj->getKey(),
        'reason' => 'payout',
    ])->validate()->execute();

    expect($updated)->not->toBeNull();
});

test('delete employee balance adjustment', function (): void {
    $adj = CreateEmployeeBalanceAdjustment::make([
        'employee_id' => $this->employee->getKey(),
        'type' => 'overtime',
        'amount' => 10.0,
        'effective_date' => '2026-07-01',
        'reason' => 'other',
    ])->validate()->execute();

    expect(DeleteEmployeeBalanceAdjustment::make(['id' => $adj->getKey()])
        ->validate()->execute())->toBeTrue();
});
