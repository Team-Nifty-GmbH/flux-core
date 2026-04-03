<?php

use FluxErp\Livewire\Widgets\Employee\VacationYearlyOverview;
use FluxErp\Models\Employee;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'employment_date' => now()->subYear(),
        'is_active' => true,
    ]);

    Livewire::test(VacationYearlyOverview::class, ['employeeId' => $employee->getKey()])
        ->assertOk();
});
