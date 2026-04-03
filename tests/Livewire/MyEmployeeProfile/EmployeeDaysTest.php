<?php

use FluxErp\Livewire\MyEmployeeProfile\EmployeeDays;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(EmployeeDays::class)
        ->assertOk();
});
