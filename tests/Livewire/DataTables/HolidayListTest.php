<?php

use FluxErp\Livewire\DataTables\HolidayList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(HolidayList::class)
        ->assertOk();
});
