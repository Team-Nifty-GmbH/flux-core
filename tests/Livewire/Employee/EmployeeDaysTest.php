<?php

use FluxErp\Livewire\Employee\EmployeeDays;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(EmployeeDays::class)
        ->assertOk();
});
