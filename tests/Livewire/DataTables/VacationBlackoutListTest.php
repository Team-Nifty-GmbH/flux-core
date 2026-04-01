<?php

use FluxErp\Livewire\DataTables\VacationBlackoutList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(VacationBlackoutList::class)
        ->assertOk();
});
