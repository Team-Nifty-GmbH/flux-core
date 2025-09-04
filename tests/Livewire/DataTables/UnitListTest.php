<?php

use FluxErp\Livewire\DataTables\UnitList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(UnitList::class)
        ->assertOk();
});
