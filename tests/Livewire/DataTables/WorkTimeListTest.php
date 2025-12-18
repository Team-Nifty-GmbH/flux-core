<?php

use FluxErp\Livewire\DataTables\WorkTimeList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(WorkTimeList::class)
        ->assertOk();
});
