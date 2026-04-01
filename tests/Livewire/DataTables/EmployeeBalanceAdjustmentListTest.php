<?php

use FluxErp\Livewire\DataTables\EmployeeBalanceAdjustmentList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(EmployeeBalanceAdjustmentList::class)
        ->assertOk();
});
