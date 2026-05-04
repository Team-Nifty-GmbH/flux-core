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
        ->assertOk()
        ->set('tab', 'employee.general')
        ->assertSee(__('Salary Type'));
});
