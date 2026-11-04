<?php

use FluxErp\Actions\Employee\CreateEmployee;
use FluxErp\Actions\Employee\DeleteEmployee;
use FluxErp\Actions\Employee\UpdateEmployee;
use FluxErp\Actions\WorkTimeModel\CreateWorkTimeModel;
use FluxErp\Models\User;

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

test('update employee links a free user and keeps its own user on save', function (): void {
    $user = User::factory()->create(['is_active' => true]);
    $employee = CreateEmployee::make([
        'firstname' => 'Link',
        'lastname' => 'Later',
        'employment_date' => '2026-01-01',
        'tenant_id' => $this->dbTenant->getKey(),
        'work_time_model_id' => $this->workTimeModel->getKey(),
    ])->validate()->execute();

    $linked = UpdateEmployee::make([
        'id' => $employee->getKey(),
        'user_id' => $user->getKey(),
    ])->validate()->execute();
    expect($linked->user_id)->toBe($user->getKey());

    // saving the same employee again with its own user must still pass
    $resaved = UpdateEmployee::make([
        'id' => $employee->getKey(),
        'user_id' => $user->getKey(),
        'firstname' => 'Still',
    ])->validate()->execute();
    expect($resaved->user_id)->toBe($user->getKey());

    // a user already linked to another employee is refused
    $other = CreateEmployee::make([
        'firstname' => 'Other',
        'lastname' => 'Person',
        'employment_date' => '2026-01-01',
        'tenant_id' => $this->dbTenant->getKey(),
        'work_time_model_id' => $this->workTimeModel->getKey(),
    ])->validate()->execute();
    UpdateEmployee::assertValidationErrors([
        'id' => $other->getKey(),
        'user_id' => $user->getKey(),
    ], ['user_id']);
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
