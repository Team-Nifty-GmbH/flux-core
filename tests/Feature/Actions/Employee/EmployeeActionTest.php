<?php

use FluxErp\Actions\Employee\CreateEmployee;
use FluxErp\Actions\Employee\DeleteEmployee;
use FluxErp\Actions\Employee\UpdateEmployee;
use FluxErp\Actions\WorkTimeModel\CreateWorkTimeModel;
use FluxErp\Models\EmployeeDay;
use FluxErp\Models\Tenant;
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

    $this->createEmployee = fn (array $data = []) => CreateEmployee::make(array_merge([
        'firstname' => 'Jane',
        'lastname' => 'Doe',
        'employment_date' => '2026-01-01',
        'tenant_id' => $this->dbTenant->getKey(),
        'work_time_model_id' => $this->workTimeModel->getKey(),
    ], $data))->validate()->execute();
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
    $employee = ($this->createEmployee)();

    $workTime = WorkTime::factory()->create([
        'user_id' => $user->getKey(),
        'started_at' => now()->subDay()->setTime(8, 0),
        'ended_at' => now()->subDay()->setTime(16, 0),
    ]);

    $linked = UpdateEmployee::make([
        'id' => $employee->getKey(),
        'user_id' => $user->getKey(),
    ])->validate()->execute();

    expect($linked->user_id)->toBe($user->getKey())
        ->and($workTime->refresh()->employee_id)->toBe($employee->getKey());
});

test('update employee links a user without any tenant', function (): void {
    $user = User::factory()->create(['is_active' => true]);
    $employee = ($this->createEmployee)();

    $linked = UpdateEmployee::make([
        'id' => $employee->getKey(),
        'user_id' => $user->getKey(),
    ])->validate()->execute();

    expect($linked->user_id)->toBe($user->getKey());
});

test('update employee keeps its own user when saved again', function (): void {
    $user = User::factory()->create(['is_active' => true]);
    $user->tenants()->attach($this->dbTenant->getKey());
    $employee = ($this->createEmployee)(['user_id' => $user->getKey()]);

    $resaved = UpdateEmployee::make([
        'id' => $employee->getKey(),
        'user_id' => $user->getKey(),
        'firstname' => 'Still',
    ])->validate()->execute();

    expect($resaved->user_id)->toBe($user->getKey());
});

test('update employee releases the work times of an unlinked user', function (): void {
    $user = User::factory()->create(['is_active' => true]);
    $user->tenants()->attach($this->dbTenant->getKey());
    $employee = ($this->createEmployee)(['user_id' => $user->getKey()]);

    $workTime = WorkTime::factory()->create([
        'user_id' => $user->getKey(),
        'employee_id' => $employee->getKey(),
        'started_at' => now()->subDay()->setTime(8, 0),
        'ended_at' => now()->subDay()->setTime(16, 0),
    ]);
    $ownWorkTime = WorkTime::factory()->create([
        'employee_id' => $employee->getKey(),
        'started_at' => now()->subDay()->setTime(8, 0),
        'ended_at' => now()->subDay()->setTime(16, 0),
    ]);
    WorkTime::query()->whereKey($ownWorkTime->getKey())->update(['user_id' => null, 'employee_id' => $employee->getKey()]);

    UpdateEmployee::make([
        'id' => $employee->getKey(),
        'user_id' => null,
    ])->validate()->execute();

    expect($workTime->refresh()->employee_id)->toBeNull()
        ->and($ownWorkTime->refresh()->employee_id)->toBe($employee->getKey());
});

test('update employee closes the affected days again when the user changes', function (): void {
    $oldUser = User::factory()->create(['is_active' => true]);
    $newUser = User::factory()->create(['is_active' => true]);
    $employee = ($this->createEmployee)(['user_id' => $oldUser->getKey()]);
    $day = now()->subDay()->startOfDay();

    WorkTime::factory()->create([
        'user_id' => $oldUser->getKey(),
        'employee_id' => $employee->getKey(),
        'is_daily_work_time' => true,
        'is_locked' => true,
        'is_pause' => false,
        'started_at' => $day->copy()->setTime(8, 0),
        'ended_at' => $day->copy()->setTime(16, 0),
    ]);
    WorkTime::factory()->create([
        'user_id' => $newUser->getKey(),
        'is_daily_work_time' => true,
        'is_locked' => true,
        'is_pause' => false,
        'started_at' => $day->copy()->setTime(9, 0),
        'ended_at' => $day->copy()->setTime(13, 0),
    ]);

    expect(EmployeeDay::query()->where('employee_id', $employee->getKey())->sole()->actual_hours)
        ->toBe('8.00');

    UpdateEmployee::make([
        'id' => $employee->getKey(),
        'user_id' => $newUser->getKey(),
    ])->validate()->execute();

    expect(EmployeeDay::query()->where('employee_id', $employee->getKey())->sole()->actual_hours)
        ->toBe('4.00');
});

test('update employee refuses a user linked to another employee', function (): void {
    $user = User::factory()->create(['is_active' => true]);
    $user->tenants()->attach($this->dbTenant->getKey());
    ($this->createEmployee)(['user_id' => $user->getKey()]);
    $employee = ($this->createEmployee)();

    UpdateEmployee::assertValidationErrors([
        'id' => $employee->getKey(),
        'user_id' => $user->getKey(),
    ], ['user_id']);
});

test('update employee refuses an inactive user', function (): void {
    $user = User::factory()->create(['is_active' => false]);
    $user->tenants()->attach($this->dbTenant->getKey());
    $employee = ($this->createEmployee)();

    UpdateEmployee::assertValidationErrors([
        'id' => $employee->getKey(),
        'user_id' => $user->getKey(),
    ], ['user_id']);
});

test('update employee refuses a user of another tenant', function (): void {
    $user = User::factory()->create(['is_active' => true]);
    $user->tenants()->attach(Tenant::factory()->create()->getKey());
    $employee = ($this->createEmployee)();

    UpdateEmployee::assertValidationErrors([
        'id' => $employee->getKey(),
        'user_id' => $user->getKey(),
    ], ['user_id']);
});

test('deleting an employee frees its user and keeps the work times with it', function (): void {
    $user = User::factory()->create(['is_active' => true]);
    $user->tenants()->attach($this->dbTenant->getKey());
    $employee = ($this->createEmployee)(['user_id' => $user->getKey()]);

    $workTime = WorkTime::factory()->create([
        'user_id' => $user->getKey(),
        'employee_id' => $employee->getKey(),
        'started_at' => now()->subDay()->setTime(8, 0),
        'ended_at' => now()->subDay()->setTime(16, 0),
    ]);

    DeleteEmployee::make(['id' => $employee->getKey()])->validate()->execute();

    expect($workTime->refresh()->employee_id)->toBe($employee->getKey());

    $other = ($this->createEmployee)(['user_id' => $user->getKey()]);

    expect($other->user_id)->toBe($user->getKey());
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
