<?php

use FluxErp\Livewire\DataTables\SalaryComponentList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(SalaryComponentList::class)
        ->assertOk();
});
