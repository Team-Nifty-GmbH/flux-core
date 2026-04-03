<?php

use FluxErp\Livewire\HumanResources\EmployeeDays;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(EmployeeDays::class)
        ->assertOk();
});
