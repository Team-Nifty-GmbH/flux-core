<?php

use FluxErp\Livewire\DataTables\AbsenceTypeList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(AbsenceTypeList::class)
        ->assertOk();
});
