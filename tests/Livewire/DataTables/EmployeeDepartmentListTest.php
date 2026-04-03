<?php

use FluxErp\Livewire\DataTables\EmployeeDepartmentList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(EmployeeDepartmentList::class)
        ->assertOk();
});
