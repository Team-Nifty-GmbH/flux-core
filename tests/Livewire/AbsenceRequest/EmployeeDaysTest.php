<?php

use FluxErp\Livewire\AbsenceRequest\EmployeeDays;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(EmployeeDays::class)
        ->assertOk();
});
