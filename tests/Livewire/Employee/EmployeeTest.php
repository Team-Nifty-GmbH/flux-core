<?php

use FluxErp\Livewire\Employee\Employee;
use FluxErp\Models\Employee as EmployeeModel;
use FluxErp\Models\User;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $employee = app(EmployeeModel::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'is_active' => true,
    ]);

    Livewire::test(Employee::class, ['id' => $employee->getKey()])
        ->assertOk()
        ->set('tab', 'employee.general')
        ->assertSee(__('Salary Type'));
});

test('mount fills form with employee data', function (): void {
    $employee = app(EmployeeModel::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Max',
        'lastname' => 'Mustermann',
        'email' => 'max@example.com',
        'is_active' => true,
    ]);

    Livewire::test(Employee::class, ['id' => $employee->getKey()])
        ->assertOk()
        ->assertSet('employee.id', $employee->getKey())
        ->assertSet('employee.firstname', 'Max')
        ->assertSet('employee.lastname', 'Mustermann')
        ->assertSet('employee.email', 'max@example.com');
});

test('can update employee via save', function (): void {
    $employee = app(EmployeeModel::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'employment_date' => now()->subYear()->format('Y-m-d'),
        'is_active' => true,
    ]);

    Livewire::test(Employee::class, ['id' => $employee->getKey()])
        ->set('employee.firstname', 'Updated')
        ->set('employee.lastname', 'Name')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    $employee->refresh();
    expect($employee->firstname)->toEqual('Updated');
    expect($employee->lastname)->toEqual('Name');
});

test('save validates required fields', function (): void {
    $employee = app(EmployeeModel::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'is_active' => true,
    ]);

    Livewire::test(Employee::class, ['id' => $employee->getKey()])
        ->set('employee.firstname', '')
        ->set('employee.lastname', '')
        ->call('save')
        ->assertReturned(false);
});

test('resetForm reloads employee data from database', function (): void {
    $employee = app(EmployeeModel::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Original',
        'lastname' => 'Employee',
        'is_active' => true,
    ]);

    Livewire::test(Employee::class, ['id' => $employee->getKey()])
        ->set('employee.firstname', 'Changed')
        ->call('resetForm')
        ->assertSet('employee.firstname', 'Original')
        ->assertSet('employee.lastname', 'Employee');
});

test('can link a user to the employee via the general form', function (): void {
    $employee = app(EmployeeModel::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'firstname' => 'Link',
        'lastname' => 'Form',
        'employment_date' => now()->subYear()->format('Y-m-d'),
        'is_active' => true,
    ]);

    $user = User::factory()->create(['is_active' => true]);
    $user->tenants()->attach($this->dbTenant->getKey());

    Livewire::test(Employee::class, ['id' => $employee->getKey()])
        ->set('employee.user_id', $user->getKey())
        ->call('save')
        ->assertHasNoErrors();

    expect($employee->refresh()->user_id)->toBe($user->getKey());
});

test('saving a stale general form keeps a user linked meanwhile', function (): void {
    $employee = app(EmployeeModel::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'firstname' => 'Keep',
        'lastname' => 'Link',
        'employment_date' => now()->subYear()->format('Y-m-d'),
        'is_active' => true,
    ]);

    $component = Livewire::test(Employee::class, ['id' => $employee->getKey()]);

    $user = User::factory()->create(['is_active' => true]);
    $employee->update(['user_id' => $user->getKey()]);

    $component
        ->set('employee.firstname', 'Renamed')
        ->call('save')
        ->assertHasNoErrors();

    expect($employee->refresh())
        ->user_id->toBe($user->getKey())
        ->firstname->toBe('Renamed');
});
