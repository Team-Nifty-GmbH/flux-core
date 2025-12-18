<?php

use FluxErp\Livewire\DataTables\CommissionList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(CommissionList::class)
        ->assertOk();
});
