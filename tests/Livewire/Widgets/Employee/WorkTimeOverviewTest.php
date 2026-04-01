<?php

use FluxErp\Livewire\Widgets\Employee\WorkTimeOverview;
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

    Livewire::test(WorkTimeOverview::class, ['employeeId' => $employee->getKey()])
        ->assertOk();
});
