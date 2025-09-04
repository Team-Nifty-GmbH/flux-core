<?php

use FluxErp\Livewire\DataTables\ScheduleList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ScheduleList::class)
        ->assertOk();
});
