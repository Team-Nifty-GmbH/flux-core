<?php

use FluxErp\Livewire\DataTables\EmployeeList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(EmployeeList::class)
        ->assertOk();
});
