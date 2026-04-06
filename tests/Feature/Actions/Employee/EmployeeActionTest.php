<?php

use FluxErp\Actions\Employee\CreateEmployee;
use FluxErp\Actions\Employee\DeleteEmployee;
use FluxErp\Actions\Employee\UpdateEmployee;
use FluxErp\Actions\WorkTimeModel\CreateWorkTimeModel;

beforeEach(function (): void {
    $this->workTimeModel = CreateWorkTimeModel::make([
        'name' => 'Standard',
        'cycle_weeks' => 1,
        'weekly_hours' => 40,
        'annual_vacation_days' => 30,
        'overtime_compensation' => 'payment',
        'is_active' => true,
    ])->validate()->execute();
});

test('create employee', function (): void {
    $employee = CreateEmployee::make([
        'firstname' => 'John',
        'lastname' => 'Doe',
        'employment_date' => '2026-01-01',
        'tenant_id' => $this->dbTenant->getKey(),
        'work_time_model_id' => $this->workTimeModel->getKey(),
    ])->validate()->execute();

    expect($employee)
        ->firstname->toBe('John')
        ->lastname->toBe('Doe');
});

test('create employee requires firstname lastname employment_date', function (): void {
    CreateEmployee::assertValidationErrors([
        'tenant_id' => $this->dbTenant->getKey(),
        'work_time_model_id' => $this->workTimeModel->getKey(),
    ], ['firstname', 'lastname', 'employment_date']);
});

test('update employee', function (): void {
    $employee = CreateEmployee::make([
        'firstname' => 'Jane',
        'lastname' => 'Smith',
        'employment_date' => '2026-01-01',
        'tenant_id' => $this->dbTenant->getKey(),
        'work_time_model_id' => $this->workTimeModel->getKey(),
    ])->validate()->execute();

    $updated = UpdateEmployee::make([
        'id' => $employee->getKey(),
        'firstname' => 'Janet',
    ])->validate()->execute();

    expect($updated->firstname)->toBe('Janet');
});

test('delete employee', function (): void {
    $employee = CreateEmployee::make([
        'firstname' => 'Temp',
        'lastname' => 'Worker',
        'employment_date' => '2026-01-01',
        'tenant_id' => $this->dbTenant->getKey(),
        'work_time_model_id' => $this->workTimeModel->getKey(),
    ])->validate()->execute();

    expect(DeleteEmployee::make(['id' => $employee->getKey()])
        ->validate()->execute())->toBeTrue();
});
