<?php

use FluxErp\Livewire\MyEmployeeProfile\MyEmployeeProfile;
use FluxErp\Models\Employee;
use Livewire\Livewire;

test('renders successfully', function (): void {
    app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'is_active' => true,
    ]);

    Livewire::test(MyEmployeeProfile::class)
        ->assertOk();
});
