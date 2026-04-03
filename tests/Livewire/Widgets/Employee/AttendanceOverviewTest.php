<?php

use FluxErp\Livewire\Widgets\Employee\AttendanceOverview;
use FluxErp\Models\Employee;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'is_active' => true,
    ]);

    Livewire::test(AttendanceOverview::class, ['employeeId' => $employee->getKey()])
        ->assertOk();
});
