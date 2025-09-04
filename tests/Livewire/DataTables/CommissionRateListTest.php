<?php

use FluxErp\Livewire\DataTables\CommissionRateList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(CommissionRateList::class)
        ->assertOk();
});
