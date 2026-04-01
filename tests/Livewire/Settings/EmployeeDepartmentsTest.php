<?php

use FluxErp\Livewire\Settings\EmployeeDepartments;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(EmployeeDepartments::class)
        ->assertOk();
});
