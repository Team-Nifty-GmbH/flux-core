<?php

use FluxErp\Livewire\Employee\Employee;
use FluxErp\Models\Employee as EmployeeModel;
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
        ->assertOk();
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
