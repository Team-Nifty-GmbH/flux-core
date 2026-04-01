<?php

use FluxErp\Livewire\DataTables\EmployeeWorkTimeModelList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(EmployeeWorkTimeModelList::class)
        ->assertOk();
});
