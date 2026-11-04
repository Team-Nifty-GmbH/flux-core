<?php

use FluxErp\Actions\Employee\CreateEmployee;
use FluxErp\Actions\Employee\DeleteEmployee;
use FluxErp\Actions\Employee\UpdateEmployee;
use FluxErp\Actions\WorkTimeModel\CreateWorkTimeModel;
use FluxErp\Models\User;
use FluxErp\Models\WorkTime;

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

test('update employee links a free user and assigns its work times', function (): void {
    $user = User::factory()->create(['is_active' => true]);
    $user->tenants()->attach($this->dbTenant->getKey());

    $employee = CreateEmployee::make([
        'firstname' => 'Link',
        'lastname' => 'Later',
        'employment_date' => '2026-01-01',
        'tenant_id' => $this->dbTenant->getKey(),
        'work_time_model_id' => $this->workTimeModel->getKey(),
    ])->validate()->execute();

    $workTime = WorkTime::factory()->create([
        'user_id' => $user->getKey(),
        'started_at' => now()->subDay()->setTime(8, 0),
        'ended_at' => now()->subDay()->setTime(16, 0),
    ]);

    $linked = UpdateEmployee::make([
        'id' => $employee->getKey(),
        'user_id' => $user->getKey(),
    ])->validate()->execute();

    expect($linked->user_id)->toBe($user->getKey());
    expect($workTime->refresh()->employee_id)->toBe($employee->getKey());
});

test('update employee keeps its user on save and releases work times when unlinked', function (): void {
    $user = User::factory()->create(['is_active' => true]);
    $user->tenants()->attach($this->dbTenant->getKey());

    $employee = CreateEmployee::make([
        'firstname' => 'Keep',
        'lastname' => 'Link',
        'employment_date' => '2026-01-01',
        'tenant_id' => $this->dbTenant->getKey(),
        'work_time_model_id' => $this->workTimeModel->getKey(),
        'user_id' => $user->getKey(),
    ])->validate()->execute();

    $workTime = WorkTime::factory()->create([
        'user_id' => $user->getKey(),
        'employee_id' => $employee->getKey(),
        'started_at' => now()->subDay()->setTime(8, 0),
        'ended_at' => now()->subDay()->setTime(16, 0),
    ]);

    $resaved = UpdateEmployee::make([
        'id' => $employee->getKey(),
        'user_id' => $user->getKey(),
        'firstname' => 'Still',
    ])->validate()->execute();

    expect($resaved->user_id)->toBe($user->getKey());

    UpdateEmployee::make([
        'id' => $employee->getKey(),
        'user_id' => null,
    ])->validate()->execute();

    expect($workTime->refresh()->employee_id)->toBeNull();
});

test('update employee refuses users that are linked, inactive or in another tenant', function (): void {
    $employee = CreateEmployee::make([
        'firstname' => 'Target',
        'lastname' => 'Employee',
        'employment_date' => '2026-01-01',
        'tenant_id' => $this->dbTenant->getKey(),
        'work_time_model_id' => $this->workTimeModel->getKey(),
    ])->validate()->execute();

    $linkedUser = User::factory()->create(['is_active' => true]);
    $linkedUser->tenants()->attach($this->dbTenant->getKey());
    CreateEmployee::make([
        'firstname' => 'Holder',
        'lastname' => 'One',
        'employment_date' => '2026-01-01',
        'tenant_id' => $this->dbTenant->getKey(),
        'work_time_model_id' => $this->workTimeModel->getKey(),
        'user_id' => $linkedUser->getKey(),
    ])->validate()->execute();

    $inactiveUser = User::factory()->create(['is_active' => false]);
    $inactiveUser->tenants()->attach($this->dbTenant->getKey());

    $otherTenantUser = User::factory()->create(['is_active' => true]);

    UpdateEmployee::assertValidationErrors([
        'id' => $employee->getKey(),
        'user_id' => $linkedUser->getKey(),
    ], ['user_id']);

    UpdateEmployee::assertValidationErrors([
        'id' => $employee->getKey(),
        'user_id' => $inactiveUser->getKey(),
    ], ['user_id']);

    UpdateEmployee::assertValidationErrors([
        'id' => $employee->getKey(),
        'user_id' => $otherTenantUser->getKey(),
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
