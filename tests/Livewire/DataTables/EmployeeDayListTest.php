<?php

use FluxErp\Livewire\DataTables\EmployeeDayList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(EmployeeDayList::class)
        ->assertOk();
});
