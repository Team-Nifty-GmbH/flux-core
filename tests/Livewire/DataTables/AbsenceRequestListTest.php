<?php

use FluxErp\Livewire\DataTables\AbsenceRequestList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(AbsenceRequestList::class)
        ->assertOk();
});
